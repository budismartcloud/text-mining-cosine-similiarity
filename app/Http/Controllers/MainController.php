<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 30/05/2018
 * Time: 5:59
 */

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use DB;
use App\Models\AuxilaryWord;
use App\Models\MergeWord;
use App\Models\MetaData;
use App\Models\Word;

class MainController extends Controller
{

    private $dataSet;
    private $mergeWordList;
    private $auxilaryWord;
    private $templateScoreingWord;
    private $documentFrequency;
    private $idfScoreTemplate;

    public function __construct()
    {
        $this->dataSet = $this->initMetaData();
        $this->mergeWordList = $this->initMergeWord();
        $this->auxilaryWord = $this->initAuxilary();
    }

    public function actionIndex()
    {
        return view('main.index');
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

        }catch (\Exception $e){
            $result = [];
            $totalPage = 0;
        }

        $params = [
            'keyword' => $keyword,
            'data' => $result['data'],
            'p' => $page,
            'totalPage' => $totalPage
        ];
        return view('main.search', $params);
    }


    private function mining($keyword, $page)
    {
        $splitedWord = explode(" ",$keyword);
        $splitedWord = $this->filteringProcess($splitedWord);
        $result = $this->analyzingProcess($splitedWord);
        $filterId = [];
        $stringOrderd = "";
        $offset = 5 * ($page - 1);

        foreach ($result as $num => $item)
        {
            if($item['sumOfScore'] > 0.0){
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

    private function filteringProcess($splitedWord)
    {
        $data = [];
        foreach ($splitedWord as $item) {
            if(!$this->isAuxilary($item)){
                $data[] = $this->steamingProcess($item);
            }
        }

        return $data;

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
        $this->templateScoreingWord = $template;

    }

    private function initDfScoringTemplate($data)
    {
        $template = [];
        foreach ($data as $num => $item) {
            $template[$item] = [
                'label' => $item,
                'score' => 0
            ];
        }
        $this->documentFrequency = $template;
        $this->idfScoreTemplate = $template;
    }

    private function analyzingProcess($sourceKeywords)
    {
        $data = [];
        foreach ($this->dataSet as $num => $content)
        {
            $this->initScoringTemplate($sourceKeywords);
            $splitedContentData = explode(" ", strtolower($content->content));
            foreach ($sourceKeywords as $no => $item){
                if(in_array(strtolower($item), $splitedContentData)){
                    $this->templateScoreingWord[$item]['score'] =+1;
                }
            }

            $data[] = [
                'metadata_id' => $content->id,
                'data' => $content,
                'result' => $this->templateScoreingWord
            ];
        }

        $this->initDfScoringTemplate($sourceKeywords);
        $this->calculateDf($data);
        $this->calculateIdf();
        return $this->calculateTdIdf($data);

    }

    private function calculateDf($data)
    {
        foreach ($data as $num => $item) {
            foreach ($item['result'] as $no => $score){
                $this->documentFrequency[$score['label']]['score'] += $score['score'];
            }
        }
    }

    private function calculateIdf()
    {
        $totalDocument = count($this->dataSet);

        foreach ($this->documentFrequency as $num => $item)
        {
            try{
                $idf = (1 + log($totalDocument/$item['score']));
            }catch (\Exception $e){
                $idf  = 0;
            }
            $this->idfScoreTemplate[$item['label']]['score'] = $idf;
        }
    }

    private function calculateTdIdf($data)
    {
        $template = [];
        foreach ($data as $num => $item){
            $scoreResume = [];
            $sumOfScoreResume = 0;
            foreach ($item['result'] as $no => $result){
                try{
                    $score = $result['score'] * $this->idfScoreTemplate[$result['label']]['score'];
                }catch (\Exception $e){
                    $score  = 0;
                }

                $scoreResume[$result['label']] = [
                    'label' => $result['label'],
                    'score' => $score
                ];

                $sumOfScoreResume += $score;
            }

            $template[] = [
                'metadata_id' => $item['metadata_id'],
                'score' =>  $scoreResume,
                'sumOfScore' => $sumOfScoreResume
            ];
        }


        $template = $this->customeArraySorter($template);

        return $template;

    }

    private function customeArraySorter($template)
    {
        array_multisort(array_map(function($element) {
            return $element['sumOfScore'];
        }, $template), SORT_DESC, $template);

        return $template;
    }


    private function steamingProcess($keyword)
    {
        $word = Word::whereRaw("lower(custom_word) LIKE '%".strtolower($keyword)."%' ")
            ->first();

        if(is_null($word)){
            return strtolower($keyword);
        }else{
            return strtolower($word->original_word);
        }

    }

    private function isAuxilary($keyword)
    {
        if(in_array($keyword, $this->auxilaryWord)){
            return true;
        }else{
            return false;
        }
    }

    private function initAuxilary()
    {
        $auxilaryWords =  AuxilaryWord::all();
        $data = [];
        foreach ($auxilaryWords as $num => $item)
        {
            $data[] = strtolower($item->word);
        }

        return $data;
    }

    private function initMergeWord()
    {

        $mergeWords =  MergeWord::all();
        $data = [];
        foreach ($mergeWords as $mergeWord) {
            $data[] = strtolower($mergeWord->first_word);
        }

        return $data;
    }

    private function initMetaData()
    {
        return MetaData::all();
    }
}