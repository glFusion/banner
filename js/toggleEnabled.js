/*  $Id: toggleEnabled.js 29 2009-11-13 00:43:27Z root $
 */
var BANR_xmlHttp;

function BANR_toggleEnabled(newval, id, type, base_url)
{
  BANR_xmlHttp = BANR_GetXmlHttpObject();
  if (BANR_xmlHttp==null) {
    alert ("Browser does not support HTTP Request")
    return
  }
  var url=base_url + "/admin/plugins/banner/ajax.php?action=toggleEnabled";
  url=url+"&id="+id;
  url=url+"&type="+type;
  url=url+"&newval="+newval;
  url=url+"&sid="+Math.random();
  BANR_xmlHttp.onreadystatechange=BANR_sc_BannerEnabled;
  BANR_xmlHttp.open("GET",url,true);
  BANR_xmlHttp.send(null);
}

function BANR_sc_BannerEnabled()
{
  var newstate;

  if (BANR_xmlHttp.readyState==4 || BANR_xmlHttp.readyState=="complete")
  {
    xmlDoc=BANR_xmlHttp.responseXML;
    id = xmlDoc.getElementsByTagName("id")[0].childNodes[0].nodeValue;
    imgurl = xmlDoc.getElementsByTagName("imgurl")[0].childNodes[0].nodeValue;
    baseurl = xmlDoc.getElementsByTagName("baseurl")[0].childNodes[0].nodeValue;
    type = xmlDoc.getElementsByTagName("type")[0].childNodes[0].nodeValue;
    if (xmlDoc.getElementsByTagName("newval")[0].childNodes[0].nodeValue == 1) {
        newval = 0;
    } else {
        newval = 1;
    }
    if (type == "cat_cb") {
        spanid = "togcatcb";
    } else {
        spanid = "togena";
    }

    //document.getElementById("togena"+id).innerHTML=
    document.getElementById(spanid + id).innerHTML=
        " <img src=\""+imgurl+"\" " +
        "style=\"display:inline; width:16px; height:16px;\" " +
        "onclick='BANR_toggleEnabled("+newval+", \""+id+"\", \""+type+"\", \""+baseurl+"\");" +
        "' /> ";
  }

}

function BANR_GetXmlHttpObject()
{
  var objXMLHttp=null
  if (window.XMLHttpRequest)
  {
    objXMLHttp=new XMLHttpRequest()
  }
  else if (window.ActiveXObject)
  {
    objXMLHttp=new ActiveXObject("Microsoft.XMLHTTP")
  }
  return objXMLHttp
}

