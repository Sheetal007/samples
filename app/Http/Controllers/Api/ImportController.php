<?php

namespace App\Http\Controllers\Api;

use App\Components\Import\ColumnsImportCompanies;
use App\Components\Import\ColumnsImportProspects;
use App\Components\Import\CsvUploader;
use App\Http\Requests\StoreImportRequest;
use App\Jobs\ImportCsv;
use Salestools\ApiDoc\ApiDocInterface;
use Salestools\ApiDoc\ApiPath;
use App\Components\Import\CsvReader;
use App\Helpers\Enum\ImportStatus;
use App\Helpers\Enum\ImportType;
use App\Http\Controllers\Controller;
use App\Models\ImportHistory;
use Auth;
use Illuminate\Http\Request;
use Response;
use Salestools\Enumerable\ObjectType;

class ImportController extends Controller implements ApiDocInterface
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * Functionality implemented is:
     *
     * Import a CSV of contacts which are added to ElasticSearch index thrugh the ImportCSV job and add entries to DB table ImportHistory to keep the record of what was uploaded.
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * Function returns all the records with limit and offset
     */
    public function index(Request $request)
    {
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 20);

        $query = ImportHistory::allowed()->orderBy('created_at', 'desc');

        $paginator = $query->offsetPaginate($offset, $limit);

        return response($paginator);
    }

    /**
     * Upload file.
     *
     * @param \App\Http\Requests\StoreImportRequest $request
     * @return Response
     * CSV file upload function using Laravel's CSV Uploader
     */
    public function store(StoreImportRequest $request)
    {
        $uploader = new CsvUploader(
            $request->file('file'),
            ImportHistory::directory(Auth::user())
        );

        $uploader->validate();

        $file = $uploader->upload();

        $type = $request->input('type');

        $config = [
            'delimiter' => $request->input('delimiter'),
            'escape' => $request->input('escape'),
            'enclosure' => $request->input('enclosure'),
        ];

        $import = new CsvReader($file, Auth::user(), $config);

        /** @var ImportHistory $history */
        $history = ImportHistory::make([
            'import_type' => ImportType::FILE,
            'object_type' => $type,
            'data' => [
                'config' => $import->getConfig(),
                'file_path' => $uploader->getFilePath(),
                'file_name' => $uploader->getOriginalFileName(),
                'file_type' => $import->getType(),
            ],
            'user_id' => Auth::user()->id,
            'team_id' => Auth::user()->team_id,
            'status' => ImportStatus::STAND_BY,
        ]);

        if ($history->isProspectType()) {
            $history->setData('sequence_id', $request->input('sequence_id'));
        }

        $history->save();

        $history['headers'] = $import->getHeadersLabels();
        $history['columns'] = $type == ObjectType::PROSPECT
            ? ColumnsImportProspects::labels()
            : ColumnsImportCompanies::labels();

        return response($history);
    }

    /**
     * Show the specified resource from storage.
     * Preview the contents of CSV
     * @param Request $request
     * @return Response
     */
    public function preview(Request $request, $id)
    {
        $this->validate($request, [
            'delimiter' => 'required|string',
            'columns' => 'required|array',
        ]);
        /** @var ImportHistory $history */
        $history = ImportHistory::allowed()->findOrFail($id);

        $config = [
            'delimiter' => $request->input('delimiter'),
            'escape' => $request->input('escape'),
            'enclosure' => $request->input('enclosure'),
        ];

        $import = new CsvReader($history->file, Auth::user(), $config);

        $history->setData('config', $import->getConfig());
        $history->setData('columns', $request->input('columns'));
        $history->save();

        return response($import->preview());
    }

    /**
     * Save the specified resource from storage.
     *
     * ImportCSV records into ElasticSearch
     *
     * @param int     $id
     * @param Request $request
     *
     * @return \Response
     */
    public function import($id, Request $request)
    {
        $this->validate($request, [
            'columns' => 'required|array',
        ]);

        /** @var ImportHistory $history */
        $history = ImportHistory::findOrFail($id);

        $history->setData('columns', $request->input('columns'));
        $history->save();

        dispatch(new ImportCsv($history));

        return response(['updated' => true]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $deleted = ImportHistory::allowed()->findOrFail($id)->delete();

        return response(['deleted' => $deleted]);
    }

    /**
     * @inheritdoc
     */
    public static function resolveApiDoc()
    {
        $tags = [__CLASS__];
        $paths = [];

        $paths['/imports/'] = [
            /* @see ImportController::index() */
            ApiPath::build($tags)->get()->description('Prospect import history'),

            /* @see ImportController::store() */
            ApiPath::build($tags)->post()->description('Upload file')
                ->param('file', 'file', true, 'File CSV')
                ->param('object_type', 'integer', true)
                ->param('sequence_id', 'integer', false)
                ->param('delimiter', 'string', false)
                ->param('escape', 'string', false)
                ->param('enclosure', 'string', false)
        ];

        $paths['/imports/{id}/preview'] = [
            /* @see ImportController::preview() */
            ApiPath::build($tags)->post()->description('Preview import file')
                ->param('id', 'integer', true, 'id', ['in' => 'path', 'default' => 1])
                ->bodyParam('data', [
                    'type' => 'object',
                    'properties' => [
                        'delimiter' => ['type' => 'string'],
                        'escape' => ['type' => 'string'],
                        'enclosure' => ['type' => 'string'],
                        'columns' => ['type' => 'array'],
                    ],
                    'example' => [
                        'delimiter' => ',',
                        'escape' => '\\',
                        'enclosure' => '"',
                        'columns' => [
                            'First Name' => 'first_name',
                            'Last Name' => 'last_name',
                            'Email' => 'emails.0.email',
                            'Phone' => 'phones.0.number',
                            'Company' => 'companies.0.name',
                            'Company Description' => 'companies.0.description',
                            'Company Website' => 'companies.0.website',
                            'Tags' => 'tags',
                        ],
                    ],
                ], true),
        ];

        $paths['/imports/{id}/import'] = [
            /* @see ImportController::import() */
            ApiPath::build($tags)->post()->description('Import prospects')
                ->param('id', 'integer', true, 'id', ['in' => 'path', 'default' => 1])
                ->bodyParam('data', [
                    'type' => 'object',
                    'properties' => [
                        'columns' => ['type' => 'array'],
                    ],
                    'example' => [
                        'columns' => [
                            'First Name' => 'first_name',
                            'Last Name' => 'last_name',
                            'Email' => 'emails.0.email',
                            'Phone' => 'phones.0.number',
                            'Company' => 'companies.0.name',
                            'Company Description' => 'companies.0.description',
                            'Company Website' => 'companies.0.website',
                            'Tags' => 'tags',
                        ],
                    ],
                ], false),
        ];

        $paths['/imports/{id}'] = [
            /* @see ImportController::destroy() */
            ApiPath::build($tags)->delete()->description('Delete import history')
                ->param('id', 'integer', true, 'id', ['in' => 'path', 'default' => 1]),
        ];

        return $paths;
    }

}
