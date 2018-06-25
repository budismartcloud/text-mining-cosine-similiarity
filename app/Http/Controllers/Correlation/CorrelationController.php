<?php

namespace App\Http\Controllers\Correlation;
use Illuminate\Http\Request;
use DB;
use App\Models\AuxilaryWord;
use App\Models\MergeWord;
use App\Models\MetaData;
use App\Models\Word;
use App\Http\Controllers\Controller;

class CorrelationController extends Controller
{

    private $dataSet;

    public function __construct()
    {
        $this->dataSet = $this->initMetaData();
    }

    public function actionIndex()
    {
        return view('correlation.index');
    }

    public function actionSearch(Request $request)
    {
        $keyword = $request->get('keyword');
        $page = $request->get('p');
        if(is_null($page)){
            $page = 1;
        }

        try{
            $result = $this->mining($keyword, $page);
            $totalPage = $result['totalData'];
            if($totalPage > 0 && $totalPage <= 5){
                $totalPage = 1;
            }elseif ($totalPage > 5){
                $tmpTotalPage = floor($totalPage / 5);
                if($totalPage %5 > 0){
                    $tmpTotalPage += 1;
                }

                $totalPage = $tmpTotalPage;
            }

            $result = $result['data'];

        }catch (\Exception $e){
            $result = [];
            $totalPage = 0;
        }

        $params = [
            'keyword' => $keyword,
            'data' => $result,
            'p' => $page,
            'totalPage' => $totalPage
        ];
        return view('correlation.search', $params);


    }

    private function mining($keyword, $page)
    {
        $splitedWord = explode(" ",$keyword);
        $result = $this->analyzingProcess($splitedWord);
        $filterId = [];
        $stringOrderd = "";
        $offset = 5 * ($page - 1);

        foreach ($result as $num => $item)
        {
            if($item['degree'] > 0.0){
                $filterId[] = $item['metadata_id'];
            }
        }

        $totalData = count($filterId);
        $selectedData = 0;
        foreach ($filterId as $num => $item){
            if($selectedData < 5){
                if($totalData > 5){
                    if($num < $offset){
                        continue;
                    }else{
                        $stringOrderd .= $item;
                        $stringOrderd .= ",";
                    }
                }else{
                    if($totalData <= 5  && $offset <= $totalData){
                        $stringOrderd .= $item;
                        $stringOrderd .= ",";
                    }elseif($totalData <= 5  && $page == 1){
                        $stringOrderd .= $item;
                        $stringOrderd .= ",";
                    }
                }
            }
            $selectedData++;
        }

        $stringOrderd .= "0";

        $resultData = MetaData::whereRaw("id IN (".$stringOrderd.")")
            ->get()->toArray();


        $displayedData = [];
        foreach ($resultData as $item) {
            $index = array_search($item['id'], $filterId);
            $displayedData[$index] = $item;
        }

        $displayedData = collect($displayedData)->sortKeys();

        $params = [
            'data' => $displayedData,
            'totalData' => $totalData
        ];

        return $params;
    }

    private function analyzingProcess($sourceKeywords)
    {
        $data = [];
        foreach ($this->dataSet as $num => $content) {
            $templateScore = $this->initScoringTemplate($sourceKeywords);
            $splitedContentData = explode(" ", strtolower($content->content));
            foreach ($sourceKeywords as $no => $item){
                if(in_array(strtolower($item), $splitedContentData)){
                    $templateScore[$item]['score'] = 1;
                }
            }

            $data[] = [
                'metadata_id' => $content->id,
                'data' => $content,
                'result' => $templateScore
            ];
        }

        $result = $this->calculateSimiliarity($data);


        return $result;
    }

    private function calculateSimiliarity($data)
    {
        $result = [];
        foreach ($data as $num => $item) {
            $tempValue = [];
            $tempScore = 0;
            foreach ($item['result'] as $no => $value){
                $score = $value['score'] * 1;
                $tempValue[$value['label']] = $score;
                $tempScore += $score;

                $cosine = $this->cosineSimiliarity($tempScore, $tempValue);
            }

            $result[] = [
                'metadata_id' => $item['metadata_id'],
                'data' => $item['data'],
                'result' => $tempValue,
                'score' => $cosine,
                'degree' => number_format(rad2deg($cosine), 2)
            ];
        }

        $response = $this->customeArraySorter($result);
        return $response;
    }

    private function customeArraySorter($template)
    {
        array_multisort(array_map(function($element) {
            return $element['degree'];
        }, $template), SORT_DESC, $template);

        return $template;
    }

    private function cosineSimiliarity($tempScore, $arrayValue)
    {
        $totalPowX = 0;
        $totalPowY = 0;
        foreach ($arrayValue as $num => $value)
        {
            $powX = pow(1, 2);
            $powY = pow($value, 2);

            $totalPowX += $powX;
            $totalPowY += $powY;
        }

        $dividen = sqrt($totalPowX) + sqrt($totalPowY);
        if($tempScore == 0 || $dividen == 0){
            $result = 0;
        }else{
            $result = $tempScore / $dividen;
        }

        return $result;
    }

    private function initScoringTemplate($data)
    {
        $template = [];
        foreach ($data as $num => $item) {
            $template[$item] = [
                'label' => $item,
                'score' => 0
            ];
        }

        return $template;
    }

    private function initMetaData()
    {
        return MetaData::all();
    }
}