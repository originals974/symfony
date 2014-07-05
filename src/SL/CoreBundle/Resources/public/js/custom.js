function sendToAjax($form) {
    var $validTarget = $($form.attr('valid-data-target'));
    var $noValidTarget = $($form.attr('no-valid-data-target'));

    $.ajax({
        type: $form.attr('method'),
        url: $form.attr('action'),
        data: $form.serializeArray(),
        success: function(data) {
            if(data.isValid)
            {
                $validTarget.append(data.html);
                $('#bootstrap_modal').modal('hide'); 
            }
            else
            {
                $noValidTarget.html(data.html);
            }
        },
        error: function(data) {
            alert('error');
        }
    });
};

