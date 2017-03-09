<?php
/*
* Check für PDFs unter Benutzung von Frank Siegerts PDFcheck
*/
class pdfCheck {

  public $checkTags = array("PageInfo" => 1,
                            "Page" => 1,
                            "Image" => 1,
                            "Source" => 1,
                            "Meta" => 1,
                            "Title" => 1,
                            "DocFont" => 1,
                            "Note" => 1,
                            "Colors" => 1,
                            "ColorReport" => 1
                            );

  function __construct($file) {
    if (is_file($file)) $this->check($file);  
  }
   
  public function check($file) {

    global $CONF;
  
    $pdfcheck_bin = "/opt/pdfcheck/pdfcheck";
    if ($CONF["pdfcheck_bin"]) $pdfcheck_bin = $CONF["pdfcheck_bin"];
    $pdfcheck_opt = "";
    if ($this->checkTags["ColorReport"]) $pdfcheck_opt = $pdfcheck_opt." -reportColors ";  
    
    $this->pdf_file = $file;      
    $this->xml_file = tempnam("/tmp", "pdfcheckXML");
    $this->tempfiles[] = $this->xml_file;
    logger::append("$pdfcheck_bin $pdfcheck_opt".$this->pdf_file." ".$this->xml_file,2);
    exec("$pdfcheck_bin $pdfcheck_opt".$this->pdf_file." ".$this->xml_file);
    $this->parseXML();
    
  }
   
  public function parseXML() {
 
    $xml_str = file_get_contents($this->xml_file);
    $p = xml_parser_create();
    xml_parser_set_option($p, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($p, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($p, $xml_str, $vals, $index);
    xml_parser_free($p);

    
    $this->rawResult = array("vals" => $vals, "index" => $index);
    
    //print_r($this->rawResult);
    
    $this->gc();
    }

  public function analyze($tags=false) {
   
    if (!$tags) $tags = $this->checkTags;
    if (is_string($tags)) $tags = array($tags => 1);
    
    if (!is_array($this->rawResult)) return false;

    if ($tags["PageInfo"]) $this->result["pageinfo"]["equalPageSize"] = true;
    if ($tags["Title"]) $this->result["title"] = array();
    if ($tags["Meta"]) $this->result["meta"] = array();
    if ($tags["Image"]) $this->result["image"] = array();
    if ($tags["DocFont"]) $this->result["font"] = array();    
    if ($tags["Note"]) $this->result["note"] = array(); 
    if ($tags["Source"]) $this->result["source"] = array(); 
    
    
    foreach($this->rawResult["vals"] as $val) {
      $tag = $val["tag"];
      
      if ($tags[$tag]) {
        
        if (is_callable(array($this,"analyze$tag"))) {
          $this->{analyze.$tag}($val);
        }
      }
    }
  }
  
  // xml Traversing -----------------------------------------------------------
  
  private function checkPage($node) {

     if ($node["tag"]!="Page") return $this->currentPage;
     
     if ($node["attributes"]["Number"]) {
        $this->currentPage = $node["attributes"]["Number"];
     }
     
     return $this->currentPage;
    
  }
  
  // Tag Analyze methods -----------------------------------------------------------
  
  private function analyzePageInfo($node) {
  
    $this->result["pageinfo"]["pagecount"]++;
    
    
    $mediabox = $this->convBoxStr($node["attributes"]["MediaBox"]);
    $cropbox = $this->convBoxStr($node["attributes"]["CropBox"]);
    $bleedbox = $this->convBoxStr($node["attributes"]["BleedBox"]);
    $trimbox = $this->convBoxStr($node["attributes"]["TrimBox"]);
    $artbox = $this->convBoxStr($node["attributes"]["ArtBox"]);
  
    $pagenum = $node["attributes"]["Num"];
    
    $this->currentpage = array("width"=>$mediabox["width"],
                               "height"=>$mediabox["height"],
                               "trimWidth"=>$trimbox["width"],
                               "trimHeight"=>$trimbox["height"]
                               );
    if ($this->lastpage and $this->lastpage!=$this->currentpage) {
      $this->result["pageinfo"]["equalPageSize"] = false;
    }
    $this->result["pageinfo"]["pages"][$pagenum] = $this->currentpage;
    
    if ($this->result["pageinfo"]["pagecount"]==1) {
      $this->result["pageinfo"]["width"] = $mediabox["width"];
      $this->result["pageinfo"]["height"] = $mediabox["height"];
      $this->result["pageinfo"]["trimWidth"] = $trimbox["width"];
      $this->result["pageinfo"]["trimHeight"] = $trimbox["height"];     
    }
    
    //echo "MediaBox: <pre>".print_r($mediabox,true)."</pre>";

    $this->lastpage = $this->currentpage;
  }
  
  private function analyzeImage($node) {
  
    $image = $node["attributes"];
    $image["onPage"] = $this->currentPage;
    $this->result["image"][] = $image;
    
  
  }
  
  private function analyzePage($node) {
  
     $this->checkPage($node);
  
  }
  
  
  private function analyzeDocFont($node) {
        
    $font = $node["attributes"];
    $font["name"] = $node["value"];
    if ($font["Embedded"] == "No") $this->result["pageinfo"]["missingFonts"] = true;
    $this->result["font"][]  = $font;
  }
  
  private function analyzeMeta($node) {    
    
    $this->result["meta"][]  = $node["attributes"];
  }
  
  private function analyzeSource($node) {    
    
    $this->result["source"][]  = $node["attributes"];
  }
  
  private function analyzeTitle($node) {    
    
    $this->result["title"][]  = $node["value"];
  }  
  
  private function analyzeNote($node) {    
    
    $this->result["note"][] = $node["value"];
  }
  
  private function analyzeColors($node) {    
    
    $this->result["colors"][] = $node["attributes"];
  } 
  
  private function analyzeColorReport($node) {
  
    foreach ($node["attributes"] as $attr => $val) {
      $this->result["pageColorReport"][$this->currentPage][$attr] = $val;
    }
  }
  
  // Tools -----------------------------------------------------
  
  private function convBoxStr($str) {
    
    $box = explode(",",$str);
    $convBox["left"] = $this->toUnit($box[0]); 
    $convBox["top"] = $this->toUnit($box[1]); 
    $convBox["width"] = $this->toUnit($box[2]); 
    $convBox["height"] = $this->toUnit($box[3]); 
    
    return $convBox;
  }
  
  private function toUnit($value) {
  
    if (!$this->unit) $this->unit = "mm";
    
    $val_conv["inch"] = $value/72;
    $val_conv["mm"] = round($val_conv["inch"] * 25.4,2);
    
    return $val_conv[$this->unit];
    
  }
  
  private function gc() {
    foreach ($this->tempfiles as $tmpfile) {
      unlink($tmpfile);
    }
  }
 
}
?>
