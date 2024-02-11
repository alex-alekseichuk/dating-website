function checkField(sField,sValue,bRequired,iMin,iMax)
{
	var strError = "";
	var sStrip = sValue;
	sStrip = sStrip.replace(/ /g, "");
	if (sStrip.length == 0 && bRequired)
		return "\tПоле " + sField + " обязательное\n";
	if (sValue.length > 0)
	{
		if (iMin > 0 && sValue.length < iMin)
			strError += "\tПоле " + sField + " должно быть не менее " + iMin + " символов\n";
		else if (iMax > 0 && sValue.length > iMax)
			strError += "\tПоле " + sField + " должно быть не более " + iMax + " символов\n";
	}
	return strError;
}
function checkSelectField(sField,iIndex)
{
	if (iIndex == 0)
		return "\tПоле " + sField + " обязательное\n";
	return "";
}
function checkEmptyValue(sField,value)
{
	if (value == "")
		return "\tПоле " + sField + " обязательное\n";
	return "";
}
function checkEmptyValue3(sField,value1,value2,value3)
{
	if (value1 == "" || value2 == "" || value3 == "")
		return "\tПоле " + sField + " обязательное\n";
	return "";
}
function checkIntField(sField,iValue,bRequired,iMin,iMax)
{
	if (iValue=="")
	{
		if (bRequired)
			return "\tПоле " + sField + " обязательное\n";
		else
			return "";
	}
	if (isNaN(iValue))
		return "\tПоле " + sField + " должно быть числовым\n";
	if (parseInt(iValue) != iValue)
		return "\tПоле " + sField + " должно быть целым числом\n";
	if (! isNaN(iMin))
		if (parseInt(iValue) < parseInt(iMin))
			return "\tПоле " + sField + " не должно быть меньше " + iMin + "\n";
	if (! isNaN(iMax))
		if (parseInt(iValue) > parseInt(iMax))
			return "\tПоле " + sField + " не должно быть больше " + iMax + "\n";
	return "";
}
function checkFloatField(sField,iValue,bRequired,iMin,iMax)
{
	if (iValue=="")
	{
		if (bRequired)
			return "\tПоле " + sField + " обязательное\n";
		else
			return "";
	}
	if (isNaN(iValue))
		return "\tПоле " + sField + " должно быть числовым\n";
	if (! isNaN(iMin))
		if (parseFloat(iValue) < parseFloat(iMin))
			return "\tПоле " + sField + " не должно быть меньше " + iMin + "\n";
	if (! isNaN(iMax))
		if (parseFloat(iValue) > parseFloat(iMax))
			return "\tПоле " + sField + " не должно быть больше " + iMax + "\n";
	return "";
}

function checkCheckField(sField,objForm,iCount)
{
	var strError = "";
	var i;
	var bCheck;
	if (iCount == '1') {
		if (objForm.checked)  bCheck = true;
	}
	else{
		for(i=0; i<iCount; i++){
			if (objForm[i].checked)  bCheck = true;
		}
	}
	if (!bCheck) strError=strError += "\tПоле " + sField + " обязательное\n";
	return strError;
}
