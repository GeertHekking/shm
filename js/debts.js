function f_detail(ip_nr)  {
    // View Details of the selected debt.
    // Hide Frame with monthly payments
    vDebt     = document.getElementById('I_debt'+ip_nr).value;
    vDescript = document.getElementById('I_description'+ip_nr).value;
    vDossier  = document.getElementById('I_dossier'+ip_nr).value;
    vAccount  = document.getElementById('I_account'+ip_nr).value;
    vAmount   = document.getElementById('I_amount'+ip_nr).value;
    vTel      = document.getElementById('I_tel'+ip_nr).value;
    vAddress  = document.getElementById('I_address'+ip_nr).value;
    
    document.getElementById('tableAppoint').style.display = 'none';
    document.getElementById('tableDet').style.display = 'block';
    
    document.getElementById('I_debt').innerHTML        = vDebt;
    document.getElementById('I_description').innerHTML = vDescript;
    document.getElementById('I_dossier').innerHTML     = vDossier;
    document.getElementById('I_account').innerHTML     = vAccount;
    document.getElementById('I_amount').innerHTML      = vAmount;
    document.getElementById('I_tel').innerHTML         = vTel;
    document.getElementById('I_address').innerHTML     = vAddress;
}

function f_afspraak(ip_nr)  {
    // View monthly payments of the selected debt.
    // Hide Frame with debt details
    vUserId    = document.getElementById('I_UserId').value;
    vDebt      = document.getElementById('I_debt'+ip_nr).value;
    vSessionId = document.getElementById('I_SessionId').value;

    document.getElementById('tableDet').style.display = 'none';
    document.getElementById('tableAppoint').style.display = 'block';

    document.getElementById('planBody').innerHTML = "<tr><td>" + vDebt + "</td></tr>";
    document.getElementById('I_Debt').value       = vDebt;
    document.getElementById('I_TotDebt').value    = document.getElementById('I_amount' + ip_nr).value; 
    // Get the plan
    $.ajax({
        url: 'https://www.gghekking.nl/shm/ajax.php?userid=' + vUserId + '&debt=' + vDebt +
             '&sessionId=' + vSessionId +
             '&functie=ovzRegeling&debt=' + vDebt,
        type: 'post',
//        data: JSON.stringify(vJSON),
        contentType: 'application/json',
        success: function (data) {
            var vRows = JSON.parse(data);
            // Connect rows to the table
            vInd = 0;
            for (var i = 0, l = vRows.debts.length; i < l; i++) {
                var vRow = vRows.debts[i];
                // ...
                vInd++;
                vNewNodeTr = document.createElement('tr');
                    // <TD>
                    vNewNodeTd = document.createElement('td');
                    vNewText   = document.createTextNode(vRow['date']);
                    vNewNodeTd.appendChild(vNewText);
                    // </TD>
                vNewNodeTr.appendChild(vNewNodeTd);
                    // <TD>
                    vNewNodeTd = document.createElement('td');
                    vNewText   = document.createTextNode(vRow['amount']);
                    vNewNodeTd.appendChild(vNewText);
                    // </TD>
                vNewNodeTr.appendChild(vNewNodeTd);
                    // <TD>
                    vNewNodeTd = document.createElement('td');
                    vNewText   = document.createTextNode(vRow['rest']);
                    vNewNodeTd.appendChild(vNewText);
                    // </TD>
                vNewNodeTr.appendChild(vNewNodeTd);
                vBody.appendChild(vNewNodeTr);
            }
            if (vInd > 0) {
                vBody.appendChild(vNewNodeTr);
            }
        },
    });
//    dataType: 'json';
    // Verwijder oude waardes
    vBody = document.getElementById('planBody');
    while (vBody.firstChild) {
        vBody.removeChild(vBody.firstChild);
    }

}

function f_fillAppointments() {
    
    // Button START has been pressed for creation of new appointment schedule 
    var vDebt = '';
    var vTotAmt     = document.getElementById('I_TotDebt').value;
    var vQtyPeriods = document.getElementById('I_QtyPeriods').value;
    var vAmtPeriod  = document.getElementById('I_AmountPeriods').value;
    var vStartDat   = new Date(document.getElementById('I_StartDate').value);
    var vRest       = vTotAmt;
    var vNextDate   = vStartDat;
    var vRows = '';
    
    // Attach new DOM elements to the table so they can be found with getElementById.
    vBody = document.getElementById('planBody');
    for (i=0; i<vQtyPeriods; i++)  {
        vAmtMonth = Math.min(vAmtPeriod, vRest);
        vRest -= vAmtMonth;
        vRest = Math.round( ( vRest + Number.EPSILON ) * 100 ) / 100
        // <TR>
        vNewNodeTr = document.createElement('tr');
            // <TH>
            vNewNodeTh = document.createElement('th');
                // <SPAN>
                vNewNodeSp = document.createElement('span');
                vNewNodeSp.setAttribute("id", "I_Date" + i);
                    vNewText   = document.createTextNode(f_dateFormat(vNextDate));
                // </SPAN>
                vNewNodeSp.appendChild(vNewText);
                vNewNodeTh.appendChild(vNewNodeSp);
            // </TH>
            vNewNodeTr.appendChild(vNewNodeTh);
        
            // <TD>
            vNewNodeTd = document.createElement('td');
                // <SPAN>
                vNewNodeSp = document.createElement('span');
                    vNewNodeSp.setAttribute("id", "I_Month" + i);
                        vNewText   = document.createTextNode(vAmtMonth);
                    vNewNodeSp.appendChild(vNewText);
                // </SPAN>
                vNewNodeTd.appendChild(vNewNodeSp);
            // </TD>
            vNewNodeTr.appendChild(vNewNodeTd);

            // <TD>
            vNewNodeTd = document.createElement('td');
                vNewText   = document.createTextNode(vRest);
                vNewNodeTd.appendChild(vNewText);
            // </TD>
            vNewNodeTr.appendChild(vNewNodeTd);
        // </TR>
        vBody.appendChild(vNewNodeTr);
        
        vNextDate.setMonth(vNextDate.getMonth() + 1);
    }
    
}

function f_dateFormat(ip_date)  {
    // Create date format dd-mm-yyyy
    var dd = ip_date.getDate();
    var mm = ip_date.getMonth()+1; 
    var yyyy = ip_date.getFullYear();

    if(dd<10) {
        dd='0'+dd;
    } 

    if(mm<10) {
        mm='0'+mm;
    }
    return(dd+'-'+mm+'-'+yyyy);
}

function f_saveAppointments()  {

    // Select scheduled values and save in the database.
    vUserId    = document.getElementById('I_UserId').value;
    vSessionId = document.getElementById('I_SessionId').value;
    vDebt      = document.getElementById('I_Debt').value;
    vRest      = document.getElementById('I_TotDebt').value;
    
    // Return JSON file
    strJSON = '{"regeling": [';
    
    // Loop through all fields
    iRow    = 0;
    vSep    = '';
    vValues = '';
    vNextElement = document.getElementById('I_Date' + iRow);
    
    while(vNextElement !== null) {
        vDateVal = vNextElement.innerHTML;
        vAmount  = document.getElementById('I_Month' + iRow).innerHTML;
        vRest   -= vAmount;
        vRest = Math.round( ( vRest + Number.EPSILON ) * 100 ) / 100;
        
        strJSON += vSep + '{"UserId": "' + vUserId + '", "Debt": "' + vDebt + '", "Date": "' + f_DateSQL(vDateVal) +
                        '", "Amount": ' + vAmount + ', "Rest": ' + vRest + ', "Payment": 0}';
        vSep = ', ';
        iRow++;
        vNextElement = document.getElementById('I_Date' + iRow);
    }
    strJSON += ']}';
    console.log(strJSON);
    vJSON = JSON.parse(strJSON);

    console.log("JSON");
    console.log(vJSON);
    $.ajax({
        url: 'https://www.gghekking.nl/shm/ajax.php?userid=' + vUserId + 
             '&sessionId=' + vSessionId +
             '&functie=Regeling&debt=' + vDebt,
        type: 'post',
        data: JSON.stringify(vJSON),
        contentType: 'application/json',
        success: function (data) {
            alert(data);
        },
    });
    dataType: 'json';

}

function f_DateSQL(ip_Date)  {
	
    var vSqlDate = '';
    vDay   = ip_Date.substr(0, 2);
    vMonth = ip_Date.substr(3, 2);
    vYear  = ip_Date.substr(6, 4);
    vSqlDate = vYear + "-" + vMonth + "-" + vDay;
	return vSqlDate;
}