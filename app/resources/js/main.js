$(function () {
    var _this = $;

    window.loader = function () {
        return '<div class="loader-icon" role="status"><i class="fa fa-spinner fa-spin"></i>' +
            '</div>';
    }
    window.removeLoder = function () {
        _this('.loader-icon').remove();
    }
    window.disableSubmit = function (element) {
        var form = element.closest("form");
        _this('[type="submit"]', form).attr('disabled', 'disabled');
    }
    window.allowSubmit = function (element) {
        var form = element.closest("form");
        if (_this('.inline-field-message', form).length == 0)
            _this('[type="submit"]', form).removeAttr('disabled');
    }



    _this('#login').validate();
    FormHandler.init({ element: '#login', response: '.response', refresh: true });

    /* create downline */
    _this('#create-user').validate({
        rules: {
            credit_limt: {
                required: true,
                number: true
            },
            confirm_password: {
                minlength: 5,
                equalTo: "#password"
            }
        }
    });
    FormHandler.init({ element: '#create-user', response: '.response', refresh: true });

    /* list users */
    $('body').on('click', '.loadchild', function (e) {
        e.preventDefault();
        var pid = _this(this).data(pid);
        var data = { 'pid': pid };
        var parentElement = _this('#loaduserinfo');
        parentElement.html(loader());
        FormHandler.GetData('/agency-management/downline-subusers', data).then(function (result) {
            removeLoder();
            parentElement.html(result);
        })
            .catch(function (message) {
                removeLoder();
            });
    });

});
