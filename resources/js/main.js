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
            } else {
                $(_obj).attr('disabled' , false);
                alert(data.message);
            }
        } catch (error) {
            $(_obj).attr('disabled' , false);
        }

    }).fail(function (data) {
        $(_obj).attr('disabled' , false);

    });




}

function mergeAccountButton(_obj){

    let btnJoin = $(_obj);
    btnJoin.attr('disabled' , true);
    $('div.loyalista_error').html('');

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
                $('div.loyalista_error').html( '<span style="color:#22bf1c">Anfrage erfolgreich erstellt.</span>');
            } else {
                $(_obj).attr('disabled' , false);
                $('div.loyalista_error').html( '<span style="color:#f40000">' + data.message  + '</span>');
            }
        } catch (error) {
            $(_obj).attr('disabled' , false);
        }

    }).fail(function (data) {
        $(_obj).attr('disabled' , false);

    });
}

function showResponse(message, error = true) {
    $('div.loyalista_response_msg').html(message).css('display', 'grid');
    if(!error) {
        $('div.loyalista_response_msg').css('color','green')
    }
}

function hideResponse() {
    $('div.loyalista_response_msg').html('').hide();
}

function register_me(_obj) {
    $(_obj).attr('disabled' , true);
    let checked = $('#yes_register_me_loyalista_program').is(":checked");
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
            if (data.success) {
                location.reload();
            } else {
                showResponse(data.message)
                $(_obj).attr('disabled' , false);
            }
        } catch (error) {
            showResponse(error)
            console.log(error);
            $(_obj).attr('disabled' , false);
        }
    }).fail(function (jqXHR, textStatus, error){
        $(_obj).attr('disabled' , false);
        if (jqXHR.status == 403) {
            let redirectLink = window.location.origin + '/login/?backlink='+window.location.href;
            window.location.href = redirectLink;
        }
    });
}

function setPointBaitUnRegistered(){
    let target_span = $('div.loyalista_checkout_content_wrapper').find('span.loyalista_co_num_of_points');
    $.ajax({
        url: '/user/total/basket/',
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
                target_span.html(data.total_cart_points);
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

function setCoPointBait(){
    let target_span = $('div.loyalista-checkout-widget_wrapper').find('span.loyalista_co_num_of_points');
    $.ajax({
        url: '/user/total/basket/',
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
                target_span.html(data.total_cart_points);
                let basket_total = data.basket_total;
                // Set Basket Total Val
                target_span.data("basket_total" ,  basket_total);
                // get current points gain
                let account_balance = target_span.data('total_redeemable_points');
                $('span.cow_account_balance').html(format(account_balance));
                $('#loyalista_widget_chekcout_option_custom_value').attr('max',account_balance );

                target_span.data("max_points" ,  account_balance);

                let point_to_val = target_span.data('point_to_value');

                let max_points = Math.round( basket_total /  point_to_val );
                if(parseFloat(max_points) < parseFloat(account_balance)){
                    target_span.data("max_points" ,  max_points);
                    $('#loyalista_widget_chekcout_option_custom_value').attr('max',max_points )
                    $('span.cow_account_balance').html(format(max_points));
                }

                $('span.cow_points_label').html(format((target_span.data("max_points") *  point_to_val )));

                // Set current points gain hint
            } else {
                console.error(data);
                // location.reload();
            }
        } catch (error) {
            console.error(error);
            location.reload();
        }
    }).fail(function (data) {
        console.error(data)
        location.reload();
    });
}

function format(num) {
    return parseFloat(num).toLocaleString("de-DE")
}

function isNumber(evt) {
    evt = (evt) ? evt : window.event;
    let charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {

        // Exception for period or decimal "."
        return (charCode === 46 && evt.target.value.indexOf('.') === -1)
    }

    return true;
}

async function redeemPoints(pointsToRedeem, pointToValue) {
    $.ajax({
        url: '/checkout/redeem/points/',
        type: "POST",
        data: {'pointsToRedeem' : pointsToRedeem, 'pointToValue' : pointToValue},
        dataType: "json",
        cache: true,
        async: false
    }).done(function (response) {
        if (response.status === 'ERROR') {
            showResponse(response.message);
        } else {
            location.reload();
        }

        return response;
    }).fail(function (data) {
        return data;
    });
}

// Get User Plentymarket Basket
function validateCustomOption(){
    let custom_value = $('input#loyalista_widget_chekcout_option_custom_value').val();
    let current_widget = $('.loyalista-checkout-widget_wrapper');
    let max_points = current_widget.find('span.loyalista_co_num_of_points').data('max_points');

    if(parseFloat(custom_value) > parseFloat(max_points) ){
        showResponse('Du kannst nicht mehr als '+max_points);
        $(this).val(max_points);
        $(this).focus();
    } else if(parseFloat(custom_value) <= 0) {
        showResponse('Bitte gebe mehr als 0 an')
        $(this).val(max_points);
        $(this).focus();
    }else{
        hideResponse();
        return true
    }

    return false;
}

function doCheckOutLoyalista() {
    let current_widget = $('.loyalista-checkout-widget_wrapper');
    let pointToValue = current_widget.find('span.loyalista_co_num_of_points').data('point_to_value');
    let selected_type = current_widget.find("input[name=loyalista_widget_chekcout__options]:checked").val();
    let total_allowed_redeemable_points = current_widget.find('span.loyalista_co_num_of_points').data('max_points');
    let basket_total_val = current_widget.find('span.loyalista_co_num_of_points').data('basket_total');

    let pointsToRedeem = 0;

    if(selected_type === 'custom') {
        if(!validateCustomOption()){
            event.preventDefault();
            return false;
        }
        pointsToRedeem = parseFloat(current_widget.find("input[name=loyalista_widget_chekcout_option_custom_value]").val().trim());
    }
    else if(selected_type === 'all')
    {
        pointsToRedeem = total_allowed_redeemable_points;
    }

    if(parseFloat(pointsToRedeem) > 0) {
        redeemPoints(pointsToRedeem, pointToValue)
            .then(response => {});
    }
}
