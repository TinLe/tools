<?php
/**
 * PHP Template.
 */
class configFormMozilla {
    var $form;
    var $left;
    var $right;

    function configFormMozilla($form, $left, $right) {
        $this->form=$form;
        $this->left=$left;
        $this->right=$right;
    }

    function aL($str) {
        return $str."\n";
    }

    function createJS($callBack1, $callBack2) {
        $str="";
        $str.=$this->aL("<script type=\"text/javascript\" language=\"javascript\" src=\"ajax/configForm/ajaxForm.js\">");
        $str.=$this->aL("</script>");
        $str.=$this->aL("<script type=\"text/javascript\">");
        $str.=$this->aL("function submit".$this->form."(action) {");
        $str.=$this->aL("  var ob=null;");
        $str.=$this->aL("  if (action==2) {");
        $str.=$this->aL("   ob=document.".$this->form."['rSelection[]'];");
        $str.=$this->aL("  } else {");
        $str.=$this->aL("   ob=document.".$this->form."['aSelection[]'];");
        $str.=$this->aL("  }");
        $str.=$this->aL("  var count=0;");
        $str.=$this->aL("  for (var i = 0; i < ob.options.length; i++) {");
        $str.=$this->aL("    if (ob.options[ i ].selected) {");
        $str.=$this->aL("      count++;");
        $str.=$this->aL("    }");
        $str.=$this->aL("  }");
        $str.=$this->aL("  if (action!=2 && action !=3) {");
        $str.=$this->aL("    document.".$this->form."['action'].value=action;");
        $str.=$this->aL("    get(document.".$this->form.",'actions/action.".$this->form.".php',callback".$this->form.");");
        $str.=$this->aL("  } else if (count==1) {");
        $str.=$this->aL("    if (action==2) {");
        $str.=$this->aL("    ".$callBack1."(ob.options[ ob.selectedIndex ])");
        $str.=$this->aL("    } else { ");
        $str.=$this->aL("    ".$callBack2."(ob.options[ ob.selectedIndex ])");
        $str.=$this->aL("    }");
        $str.=$this->aL("  }");
        $str.=$this->aL("}");
        $str.=$this->aL("function callback".$this->form."() {");
        $str.=$this->aL("  if (http_request.readyState == 4) {");
        $str.=$this->aL("    if (http_request.status == 200) {");
        $str.=$this->aL("      var result = http_request.responseText;");
//$str.=$this->aL("alert (result);"); //Debug method
        $str.=$this->aL("    ".$callBack1."('null');");
        $str.=$this->aL("      var split = result.split(\"?\");");
        $str.=$this->aL("      if (split.length != 2) {");
        $str.=$this->aL("           alert('wrong ajax data');");
        $str.=$this->aL("      } else {");
        $str.=$this->aL("           document.".$this->form.".elements['rSelection[]'].innerHTML=split[0];");
        $str.=$this->aL("           document.".$this->form.".elements['aSelection[]'].innerHTML=split[1];");
        $str.=$this->aL("      }");
        $str.=$this->aL("    } else {");
        $str.=$this->aL("      alert('There was a problem with the request.');");
        $str.=$this->aL("    }");
        $str.=$this->aL("  }");
        $str.=$this->aL("}");
        $str.=$this->aL("</script>");
        return $str;
    }
    function createFormTable($info, $title) {
        $label_left=array();
        $label_right=array();
        foreach($this->left as $val) {
            $label_left[]=$val;
        }
        foreach($this->right as $val) {
            $label_right[]=$val;
        }
        $str="";
        $str.=$this->aL($this->createFormTableCustom($info, $label_left, $label_right,$title));
        return $str;
    }

    function createFormTableCustom($info,$label_left,$label_right,$title) {
        $str="";
        $str.=$this->aL("<table width=\"650px\" cellspacing=\"0\" cellpadding=\"2\">");
        $str.=$this->aL("<tr>");
        $str.=$this->aL("<td colspan=\"3\"><br><b></td>");
        $str.=$this->aL("</tr>");
        $str.=$this->aL("<tr>");
        $str.=$this->aL("<td colspan=\"3\"><label>".$title."</label></td>");
        $str.=$this->aL("</tr>");
        $str.=$this->aL("<tr>");
        $str.=$this->aL("<td colspan=\"3\"><hr><b></td>");
        $str.=$this->aL("</tr>");
        $str.=$this->aL("<tr>");
        $str.=$this->aL("<form name=\"".$this->form."\">");
        while (list($key, $val) = each($info)) {
            $str.=$this->aL("<input type=\"hidden\" name=\"".$key."\" value=\"".$val."\">");
        }

        $str.=$this->aL("<input type=\"hidden\" name=\"action\" value=\"\">");
        $str.=$this->aL("<td align=\"center\">");
        $str.=$this->aL("<select name=\"rSelection[]\" size=\"5\" style=\"width:150px\" onchange=\"submit".$this->form."(2)\" multiple>");
        $str.=$this->createOptions1($label_left);
        $str.=$this->aL("</select>");
        $str.=$this->aL("</td>");
        $str.=$this->aL("<td width=\"150\">");
        $str.=$this->aL("<a href=\"javascript:submit".$this->form."(1);\" class=\"button\"><span> Remove >> </span></a>");
        $str.=$this->aL("<br>");
        $str.=$this->aL("<a href=\"javascript:submit".$this->form."(0);\" class=\"button\"><span> << &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp Add  </span></a>");
        $str.=$this->aL("</td>");
        $str.=$this->aL("<td align=\"center\">");
        $str.=$this->aL("<select name=\"aSelection[]\" size=\"5\" style=\"width:150px\" onchange=\"submit".$this->form."(3)\" multiple>");
        $str.=$this->createOptions2($label_right);
        $str.=$this->aL("</select>");
        $str.=$this->aL("</td>");
        $str.=$this->aL("</form>");
        $str.=$this->aL("</tr>");
        $str.=$this->aL("</table>");

        return $str;
    }

    function createOptions1($label_left) {
        $str="";
        for ($i=0;$i<count($this->left);$i++) {
            $str.=$this->aL("<option value='".$this->left[$i]."'>".$label_left[$i]."</option>");
        }
        return $str;
    }

    function createOptions2($label_right) {
        $str="";
        for ($i=0;$i<count($this->right);$i++) {
            $str.=$this->aL("<option value='".$this->right[$i]."' >".$label_right[$i]."</option>");
        }

        return $str;
    }
}
?>