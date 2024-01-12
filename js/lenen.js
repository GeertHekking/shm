var vLast = 0;
var vPlus = 3;
var vCol1;

function f_details(ip_row, ip_Id)  {
    // View Details of the selected loan.
    var vTable = document.getElementById('MainTable');
    if (vLast > 0) {
        // Delete previous detail line
        vTable.deleteRow(vLast);
    }

    if (vLast != ip_row + vPlus) {
        
        var vRow   = vTable.insertRow(ip_row + vPlus);
        vLast = ip_row + vPlus;
        // Add column with overview, collected by Ajax call.
        var vCol   = vRow.insertCell(0);
        vCol.colSpan = 4;
        vCol1 = vCol;

        // Ajax call to get all payments for this row.
        vSessionId = document.getElementById('I_SessionId').value;
        vUserId = document.getElementById('I_UserId').value;
        console.log('Upload data for Debt with ID ' + ip_Id);
        $.ajax({
            url: 'https://www.gghekking.nl/shm/ajax.php?userid=' + vUserId + 
                 '&sessionId=' + vSessionId +
                 '&functie=leenPayed&leenId=' + ip_Id,
            type: 'get',
            // data: JSON.stringify(vPayed),
            contentType: 'application/json',

            success: function (data) {
                vBetalingen = JSON.parse(data);
                // alert(data);
                vBetalingen = JSON.parse(data);
                console.log(data);
                console.log('Aantal betalingen: ' + vBetalingen.payments.length);
                vTable = '<table width=100% class="table table-dark"><tr><th>Datum</th><th>Bedrag</th><th>Door</th></tr>';
                for (i=0; i< vBetalingen.payments.length; i++) {
                  vRow = vBetalingen.payments[i];
                  vTable += '<tr><td scope="col">' + vRow.date + '</td><td scope="col">' + vRow.amount + '</td><td scope="col">' + vRow.from + '</td><td><button type="button" class="btn btn-danger" onclick="f_delBetaling(' + ip_Id + ', ' + vRow.payID + ')">Del</button></td></tr>';
                }
                vTable += '</table>';
                vCol.innerHTML = vTable;
            },
        });
    
        vCol2 = vRow.insertCell(1);
        vCol2.colSpan = 2;
        vCol2.innerHTML = '<input type=number value=0.00 id="up_' + ip_Id + '"><button type="button" class="btn btn-success" onclick="f_setBetaling(' + ip_Id + ', ' + vRow.payID + ')">Betaling</button>';
    
    } else {
        vLast = 0;
    }

}

function f_setBetaling(ip_Id)  {
    // Ajax call to get all payments for this row.
    console.log('Payment for ID ' + ip_Id);
    vSessionId = document.getElementById('I_SessionId').value;
    vUserId    = document.getElementById('I_UserId').value;
    vPayment   = document.getElementById('up_'+ip_Id).value;

    console.log('Payment of ' + vPayment + ' for ID ' + ip_Id);
    $.ajax({
        url: 'https://www.gghekking.nl/shm/ajax.php?userid=' + vUserId + 
             '&sessionId=' + vSessionId +
             '&functie=leenPayment&leenId=' + ip_Id + '&payAmount=' + vPayment,
        type: 'get',
        contentType: 'application/json',

        success: function (data) {
            vBetalingen = JSON.parse(data);
            console.log(data);
            console.log('Aantal betalingen: ' + vBetalingen.payments.length);
            vTable = '<table width=100% class="table table-dark"><tr><th>Datum</th><th>Bedrag</th><th>Door</th></tr>';
            for (i=0; i< vBetalingen.payments.length; i++) {
                  vRow = vBetalingen.payments[i];
                  vTable += '<tr><td scope="col">' + vRow.date + '</td><td scope="col">' + vRow.amount + '</td><td scope="col">' + vRow.from + '</td><td><button type="button" class="btn btn-danger" onclick="f_delBetaling(' + ip_Id + ', ' + vRow.payID + ')">Del</button></td></tr>';
            }
            vTable += '</table>';
            vCol1.innerHTML = vTable;
        },
    });
}

function f_delBetaling(ip_parentId, ip_Id)  {
    // Ajax call to get all payments for this row.
    // ip_parentId is the ID of the debt.
    // ip_Id is the ID for the payment, which needs to be deleted

    console.log('Delete Post ID ' + ip_parentId + 'Payment ID ' + ip_Id);
    vSessionId = document.getElementById('I_SessionId').value;
    vUserId    = document.getElementById('I_UserId').value;
    
    $.ajax({
        url: 'https://www.gghekking.nl/shm/ajax.php?userid=' + vUserId + 
                '&sessionId=' + vSessionId +
                '&functie=leenPayDel&leenId=' + ip_parentId + '&delId=' + ip_Id,
        type: 'get',
        contentType: 'application/json',

        success: function (data) {
            vBetalingen = JSON.parse(data);
            console.log(data);
            console.log('Aantal betalingen: ' + vBetalingen.payments.length);
            vTable = '<table width=100% class="table table-dark"><tr><th>Datum</th><th>Bedrag</th><th>Door</th></tr>';
            for (i=0; i< vBetalingen.payments.length; i++) {
                    vRow = vBetalingen.payments[i];
                    vTable += '<tr><td scope="col">' + vRow.date + '</td><td scope="col">' + vRow.amount + '</td><td scope="col">' + vRow.from + '</td><td><button type="button" class="btn btn-danger" onclick="f_delBetaling(' + ip_parentId + ', ' + vRow.payID + ')">Del</button></td></tr>';
            }
            vTable += '</table>';
            vCol1.innerHTML = vTable;
        },
    });
    
}
