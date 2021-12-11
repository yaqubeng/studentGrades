<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use File;

use Elasticsearch\ClientBuilder;
class HomeController extends Controller
{
public $doc_path; 
public $client;

function __construct() {
    $this->doc_path=storage_path()."/App/pdfs/";

    $this->client =ClientBuilder::create()->setElasticCloudId("KorekTest:dXMtY2VudHJhbDEuZ2NwLmNsb3VkLmVzLmlvJDY1M2VhYzJkZTY4MzQwMGI4ZTU5ZjNlYmY1NDZjNTQwJDc3ZmIxMDJlYWI5OTRiY2RhMGZmMDFkMjRhZjA3ZGI1")->setBasicAuthentication('elastic', '286USki3vIdRKBilbKzqMECq')->build();
}

//  public function index(){
      
//      return $this->doc_path;
//  }



    public function upload(Request $request){
        $isUpload=0;
        
              if($request->hasFile('formFile'))
              {
                  $files = $request->file('formFile');
                  foreach($files as $file)
                  {
                    $fileExt = $file->getClientOriginalExtension();
                    //   $currentT=time()+10800;

                      $fileName=$file->getClientOriginalName();

                      $fileName=str_replace(" ","_",$fileName);
                      if($fileExt=='pdf'){
                      if($file->storeAs('pdfs/', $fileName)){
                        $this->ingest_processor_indexing($fileName);
                          $isUpload++;
                      }
                      }
                  }
               }
      return $isUpload;
          }

    public function ingest_processor_mapping()
    {
        

        $params = [
            'id' => 'attachment',
            'body' => [
                'description' => 'Extract attachment information',
                'processors' => [
                    [
                        'attachment' => [
                            'field' => 'content',
                            'indexed_chars' => -1
                        ]
                    ]
                ]
            ],
            
        ];
        return $this->client->ingest()->putPipeline($params);
    }

    public function ingest_processor_indexing($fileName)
    {
        $fullfile =$this->doc_path.$fileName;
$filecontent=file_get_contents($fullfile);
        $params = [
            'index' => $fileName,
            'type'  => 'attachment',
            'id'    => $fileName,
            'pipeline' => 'attachment',
            'body'  => [
                'content' => base64_encode($filecontent),
                'file_path' =>$fullfile,
            ]
        ];
         if($this->client->index($params)){return 1;}
    }

public function ingest_processor_searching(Request $request , $searchKey)
{

if(preg_match("/[a-z]/i", $searchKey)){
     $searchKey=trim(strtolower($searchKey));
}
$searchKey=explode(' ' , $searchKey)[0];

    $params=[
    'body'=>[
        'query'=>[
            "wildcard"=>[
                "attachment.content"=>[
                    "value"=>"*$searchKey*",
                    "boost"=> 1.0,
                    "rewrite"=> "constant_score"
                ]
                ]
                ],
                'highlight' => [
                    "boundary_scanner_locale"=>"zh_CN",
                    "boundary_scanner"=>"sentence",
                    "fragmenter"=>"span", 
                    'pre_tags' => ["<mark>"], // not required
                    'post_tags' => ["</mark>"], // not required
                    'fields' => [
                        'attachment.content' => [
                            "type"=> "plain",
                            "fragment_size"=> 20,
                            "number_of_fragments"=>20
                        ]
                        
                    ],
                    // 'require_field_match' => true
                ]
                ]
                  ];

    $response = $this->client->search($params);
    $docs=$response['hits']['hits'];
    $list = array();
    foreach($docs as $doc){
        $temp=array();
        array_push($temp,$doc['_index']);
        array_push($temp,$doc['highlight']['attachment.content']);
        array_push($list,$temp);
            }
            // return dd($list);
            return json_encode($list);
}

function allindexs(){
    $params=['index'=>'*'];
    $response=$this->client->indices()->get($params);
    $response=array_keys($response);
    // dd($response);
    return json_encode($response);
}


public function download(Request $request , $file){
    return response()->file($this->doc_path.$file);
    // return response()->download($this->doc_path.$file);
}

public function deletePdf(Request $request , $file){

$params = ['index' => $file];


    if(File::exists($this->doc_path.$file)){

    $response = $this->client->indices()->delete($params);
        return File::delete($this->doc_path.$file);
    }else{
        dd('File does not exists.');
    }

}

public function pdfStream(Request $request){
    $user = UserDetail::find($user->id);
    $data["info"] = $user;
    $pdf = PDF::loadView('korek', $data);
    return $pdf->stream('korek.pdf');
  }

}
