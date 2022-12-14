function unRegisterMe(_obj) {

    $(_obj).attr('disabled' , true);

    let checked = $('#yes_unreg_loyalista_pro').is(":checked");

    if(!checked){
        $(_obj).attr('disabled' , false);
        return false;
    }

    $.ajax({
        url: '/account/unregister/customer/',
        type: "GET",
        data: {},
        dataType: "json",
        cache: true
    }).done(function (return_data) {
        try {

            var data;

            if (typeof return_data === 'object') data = return_data;
            else data = $.parseJSON(return_data);

            if (data.status === "OK") {
                location.reload();
                console.log('done');
            } else {
                $(_obj).attr('disabled' , false);
                alert(data.message);
            }
        } catch (error) {
            $(_obj).attr('disabled' , false);
            console.log(error);
        }

    }).fail(function (data) {
        $(_obj).attr('disabled' , false);

    });




}

function mergeAccountButton(_obj){

    let btnJoin = $(_obj);
    btnJoin.attr('disabled' , true);
    $('div#loyalista_error').html('');

    let $email_input =  $('input[id=merge_loyalista_acc_btn]');
    let $customer_email_address = $email_input.val().trim();

    if ($customer_email_address === ""){
        // Invalid operation
        $email_input.focus();
        btnJoin.attr('disabled' , false);
    }

    $.ajax({
        url: '/account/merge/customer/',
        type: "POST",
        data: {'customer_email_address' : $customer_email_address},
        dataType: "json",
        cache: true
    }).done(function (return_data) {
        try {

            var data;

            if (typeof return_data === 'object') data = return_data;
            else data = $.parseJSON(return_data);

            if (data.status === "OK") {
                $('div#loyalista_error').html( '<span style="color:#22bf1c">Request created successfully</span>');
            } else {
                $(_obj).attr('disabled' , false);
                $('div#loyalista_error').html( '<span style="color:#f40000">' + data.message  + '</span>');
            }
        } catch (error) {
            $(_obj).attr('disabled' , false);
            console.log(error);
        }

    }).fail(function (data) {
        $(_obj).attr('disabled' , false);

    });
}

function register_me(_obj) {
    $(_obj).attr('disabled' , true);

    let checked = $('#yes_rgisistered_loyalista_programme').is(":checked");

    if(!checked){
        $(_obj).attr('disabled' , false);
        return false;
    }

    $.ajax({
        url: '/account/register/customer/',
        type: "GET",
        data: {},
        dataType: "json",
        cache: true
    }).done(function (return_data) {
        try {
            var data;
            if (typeof return_data === 'object') data = return_data;
            else data = $.parseJSON(return_data);
            if (data.status === "OK") {
                location.reload();
            } else {
                $(_obj).attr('disabled' , false);
                alert(data.message);
            }
        } catch (error) {
            console.log(error);
            $(_obj).attr('disabled' , false);
        }
    }).fail(function (data) {
        $(_obj).attr('disabled' , false);
    });
}
