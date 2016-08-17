function errorMessage(errors) {        
    var message = '';   
    
    try {
        var json = JSON.parse(errors)
    } catch (e) {}   
    
    if (json) {        
        for (i in json) {
            message += json[i] + "<br/>";
        }        
    } else {
        message = errors;
    }
    
    $('#error-dialog .modal-body').html(message);
    $('#error-dialog').modal('show');
}

function successMessage(message) {
    $('#message-dialog .modal-body').html(message);
    $('#message-dialog').modal('show');
}

$(document).ready(function() {
    $('#checkin-btn').on('click', function() {
        var lot   = $('#checkin_parking_lot').val();
        var plate = $('#checkin_license_plate').val();
        var type  = $('#type').val();

        $.ajax({
            url: '/api/parking/park',
            method: 'POST',
            data: { parking_lot_id: lot, license_plate: plate, type: type },
            success: function(response) {
                var json = JSON.parse(response);

                if (json.hasOwnProperty('error')) {
                    errorMessage(json.error);
                } else {
                    successMessage("Car has checked in at " + json.check_in + " in " + json.parking_lot.name);
                }
            }
        });
    });

    $('#checkout-btn').on('click', function() {
        var lot   = $('#checkout_parking_lot').val();
        var plate = $('#checkout_license_plate').val();

        $.ajax({
            url: '/api/parking/park',
            method: 'PUT',
            data: { parking_lot_id: lot, license_plate: plate },
            success: function(response) {
                var json = JSON.parse(response);

                if (json.hasOwnProperty('error')) {
                    errorMessage(json.error);
                } else {
                    var duration = json.duration;
                    var hours    = Math.floor((duration / 60));
                    var minutes  = duration - (hours * 60);
                    
                    successMessage("Car has checked out at " + json.check_out + ". Duration: " + hours + " hours and " + minutes + " minute(s) for $" + json.amount);
                }
            }
        });
    });
    
    $('#find-btn').on('click', function() {
        var plate = $('#find_license_plate').val();
        
        $.ajax({
            url: '/api/parking/park',
            method: 'GET',
            data: { license_plate: plate },
            success: function(response) {
                var json = JSON.parse(response);

                if (json.hasOwnProperty('error')) {
                    errorMessage(json.error);
                } else {
                    var duration = json.duration;
                    var hours    = Math.floor((duration / 60));
                    var minutes  = duration - (hours * 60);                    
                    
                    successMessage("Car is at " + json.parking_lot.name + ". Checked in at: " + json.check_in + " Duration: " + hours + " hours and " + minutes + " minute(s) for $" + json.amount);
                }
            }
        });        
    });
});