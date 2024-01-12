function f_updBank(ip_Nr)  {
    // Get values
    vBank   = document.getElementById('bank_'+ip_Nr).innerHTML;
    vAmount = document.getElementById('amount_'+ip_Nr).innerHTML;
    vPageId = document.getElementById('I_PageId').value;
    vUserId = document.getElementById('I_UserId').value;
    
    // Paint overlay
    var newUpdDiv = document.createElement("div");
	newUpdDiv.style.display = "none";
	newUpdDiv.id = "updBank";	

	var overlay = createOverlay();
	overlay.id = "overlay";
	newUpdDiv.appendChild(overlay);

	var dialog = document.createElement("div");
	dialog.className = "center";

	var windowHTML = "<h1 style='text-align: center; border-bottom: 1px solid black; marign-bottom: 0'>bijwerken Bezitting</h1>";
		windowHTML += "<div class='updBank' style='align: center; background: white'>";
        windowHTML += "<form action='https://www.gghekking.nl/shm/start.php?function=finmndovz&pageid=" + vPageId + "&userid=" + vUserId + "' method='POST'>";
		windowHTML += "<h2>" + vBank + "</h2>";
        windowHTML += "<input type=\"hidden\" id=\"I_UserId\"  name=\"I_UserId\" value=\"" + vUserId + "\">";
        windowHTML += "<input type=\"hidden\" id=\"I_pageId\"  name=\"I_pageId\" value=\"" + vPageId + "\">";
        windowHTML += "<input type=\"hidden\" id=\"I_Bank\"  name=\"I_Bank\" value=\"" + vBank + "\">";
        windowHTML += "<input type='number'  placeholder=\"0.00\" pattern=\"^\d+(?:\.\d{1,2})?$\" min=\"-99999\" step=\"0.01\" id='I_Amount' name='I_Amount' value=" + vAmount + ">";
		windowHTML += "<br><table class='buttons'><tr><td><input type='button' value='Reset' onclick='removeDialog();'>&nbsp;&nbsp;&nbsp;&nbsp;<input type='Submit' id='SubBank' name='SubBank' value='Opslaan'>";
		windowHTML += "</td></tr></table></form></div>";

	dialog.innerHTML = windowHTML;
	newUpdDiv.appendChild(dialog);

	document.body.appendChild(newUpdDiv);
	newUpdDiv.style.display = "block";

}

function f_payed(ip_post, ip_amount, ip_nr, ip_type) {
    
    vSessionId = document.getElementById('I_PageId').value;
    vPageId = document.getElementById('I_PageId').value;
    vUserId = document.getElementById('I_UserId').value;
    vMonth  = document.getElementById('I_Month').value;
    vYear   = document.getElementById('I_Year').value;
	vPayedH = document.getElementById('payed_' + ip_nr);
    vPayedPrev = "<span id='payed_" + ip_nr + "'>";
    vPayPart = document.getElementById('I_PartPay').value;
    var vPayed = {month: vMonth, year: vYear, amount: ip_amount, post: ip_post, partPay: vPayPart};
    // var vJSON = JSON.stringify(oPayed);
    console.log(JSON.stringify(vPayed));
    $.ajax({
        url: 'https://www.gghekking.nl/shm/ajax.php?userid=' + vUserId + 
             '&sessionId=' + vSessionId +
             '&functie=payed&month=' + vMonth + '&year=' + vYear + 
             '&post=' + ip_post + '&amount=' + ip_amount,
        type: 'post',
        data: JSON.stringify(vPayed),
        contentType: 'application/json',
        success: function (data) {
//            alert(data);
			if (data.substr(0, 5) == 'Payed') {
				vAmount = data.substr(6, data.length - 6);
				
				// Update payed amount for current post.
            	vPayedH  = document.getElementById('payed_' + ip_nr);
                vNewText = document.createTextNode(vAmount);
                vPayedH.appendChild(vNewText);
				// vPayedH.innerHTML = vAmount;
			}
        },
    });
    dataType: 'json';
	
	// Show payment on screen
	

}

function f_open(ip_post, ip_nr)  {
    
    vPageId    = document.getElementById('I_PageId').value;
    vUserId    = document.getElementById('I_UserId').value;
    vMonth     = document.getElementById('I_Month').value;
    vYear      = document.getElementById('I_Year').value;
    
    var vUnPayed = {month: vMonth, year: vYear, post: ip_post};
    console.log(JSON.stringify(vUnPayed));
    $.ajax({
        url: 'https://www.gghekking.nl/shm/ajax.php?userid=' + vUserId + 
             '&sessionId=' + vPageId +
             '&functie=unpayed&month=' + vMonth + '&year=' + vYear + 
             '&post=' + ip_post,
        type: 'post',
        data: JSON.stringify(vUnPayed),
        contentType: 'application/json',
        success: function (data) {
			if (data.substr(0, 7) == 'UnPayed') {
				// Update payed amount for current post.
            	vPayedH  = document.getElementById('payed_' + ip_nr);
                if (vPayedH == null) {
                    // Create element payed_ ip_nr.
                }
                vPayedH.textContent = '0.00';
			}
        },
    });
    dataType: 'json';
	
	// Show payment on screen
	

}

function removeDialog()  {
	var updDiv = document.getElementById("updBank");
	document.body.removeChild(agendaDiv);
	return false;
}

function createOverlay()  {
	var div = document.createElement("div");
	div.className = "grayout";
	document.body.appendChild(div);
	return div;
}