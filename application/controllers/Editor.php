<?php

class Editor extends CI_Controller
{
    public function index(){
        $this->load->model('Auth');
        if($this->Auth->islogged() == true){
            $uId = $_SESSION['uId'];
            if(array_key_exists('project',$_GET)) {
                $pId = $_GET['project'];
                $this->load->model('EditorM');
                $response = $this->EditorM->get_sentences($uId,$pId);
                $sentences = $response['sentence'];
                $skips = $response['skip'];
                $translated = $response['translated'];
                $skip = [];
                if(!empty($skips)) {
                    foreach ($skips->result() as $row) {
                        array_push($skip, $row->sId);
                    }
                }
                $translate = [];
                if(!empty($translated)) {
                    foreach ($translated->result() as $row) {
                        array_push($translate, $row->sId);
                    }
                }
                $data=[];
                foreach ($sentences->result() as $row){
                    if(!in_array($row->sId, $skip) and !in_array($row->sId, $translate)){
                        $data['sId'] = $row->sId;
                        $data['pId'] = $row->projectId;
                        $data['uId'] = $uId;
                        $data['source'] = $row->sourceSentence;//'মেসির সঙ্গে আমি খেলতে পেরেছি সেটা আমার জন্য বিশাল একটি সম্মান';//
                        //$data['source'] = 'পাঁচ দিন চলেছে অনুষ্ঠান';
                        break;
                    }
                }
                if(empty($data['source'])){
                    $this->session->set_flashdata('message','At first, Please Upload text file to translate');
                    redirect(base_url()."ProjectSettings?project=".$pId);
                }
                $sentence = str_replace(',','',$data['source']);
                $sentence_list = explode('।', $sentence);
                $glossary = [];
                for($i=0; $i<count($sentence_list);$i++){
                    $words = explode(' ',trim($sentence_list[$i]));
                    for($j=0; $j<count($words);$j++){
                        $output = '';
                        $meaning = $this->EditorM->get_raw_meaning($words[$j]);
                        if($meaning->num_rows > 0){
                            while ($row = mysqli_fetch_assoc($meaning)){
                                extract($row);
                                $output .= $enUS.", ";
                            }
                            $output = trim($output);
                            $output = substr($output,0,strlen($output)-strlen(','));
                            $arr = array($words[$j], $output);
                            array_push($glossary,$arr);
                        }else {
                            $root = $this->get_root($words[$j]);
                            $end_vowel = $this->ends_with_vowel($root);
                            if($end_vowel == false) {
                                $root2 = $root . 'া';
                            }else{
                                $root2= $root;
                            }
                            $meaning = $this->EditorM->get_raw_meaning($root2);
                            if($meaning->num_rows > 0){
                                while ($row = mysqli_fetch_assoc($meaning)){
                                    extract($row);
                                    $output .= $enUS.", ";
                                }
                                $output = trim($output);
                                $output = substr($output,0,strlen($output)-strlen(','));
                                $arr = array($root2, $output);
                                array_push($glossary,$arr);
                            }else {
                                /*if($root=='খ' or $root == 'খা' or $root=='খে' or $root == 'খাও'){
                                    //
                                }elseif ($root=='দ' or $root == 'দে' or $root=='দাও' or $root == 'দি' or $root=='দা' or $root == ''){
                                    //
                                }*/
                                $meaning = $this->EditorM->get_raw_meaning($root);
                                if($meaning->num_rows > 0) {
                                    while ($row = mysqli_fetch_assoc($meaning)) {
                                        extract($row);
                                        $output .= $enUS . ", ";
                                    }
                                    $output = trim($output);
                                    $output = substr($output,0,strlen($output)-strlen(','));
                                    $arr = array($root, $output);
                                    array_push($glossary, $arr);
                                }else{
                                    $root_ = substr($root,0,6);
                                    $root_ = str_replace('ে','া',$root_);
                                    $root = $root_.substr($root,6);
                                    $end_vowel = $this->ends_with_vowel($root);
                                    if($end_vowel == false) {
                                        $root2 = $root . 'া';
                                    }else{
                                        $root2= $root;
                                    }
                                    $meaning = $this->EditorM->get_raw_meaning($root2);
                                    if($meaning->num_rows > 0){
                                        while ($row = mysqli_fetch_assoc($meaning)) {
                                            extract($row);
                                            $output .= $enUS . ", ";
                                        }
                                        $output = trim($output);
                                        $output = substr($output,0,strlen($output)-strlen(','));
                                        $arr = array($root2, $output);
                                        array_push($glossary, $arr);
                                    }
                                }
                            }
                        }
                    }
                }
                $data['glossary'] = $glossary;
                //$fuzzy_sugges = $this->get_TM_data($sentence_list);
                $tm_data = $this->EditorM->get_tm_sentences();
                $fuzzy_sugges = $this->approximate_matching($tm_data, $sentence_list);
                $data["en_suggestion"] = $fuzzy_sugges['en'];
                $data["bn_suggestion"] = $fuzzy_sugges['bn'];
                $this->load->view('editor_v', $data);
            }else{
                redirect(base_url().'dashboard');
            }
        }else {
            redirect(base_url().'login');
        }
    }
    function approximate_matching($trans_sent, $target_sent){
        $output_sentence_listBN = [];
        $output_sentence_listEN = [];
        $words = [];
        $this->load->model('EditorM');
        for($i=0; $i < count($target_sent); $i++){
            $word = explode(" ", trim($target_sent[$i]));
            foreach ($word as $w){
                array_push($words, $w);
            }
        }
        foreach ($trans_sent->result() as $row){
            $count = 0;
            $bn_sentence = $row->sourceSentence;
            $en_sentence = $row->targetText;
            $trans_words = explode(' ', trim($bn_sentence));
            $en_words = explode(' ', str_replace('.','',trim($en_sentence)));
            for($i=0; $i<count($words); $i++){
                foreach ($trans_words as $word){
                    $distance = $this->edit_distance(trim($word), trim($words[$i]));
                    if($distance <= 3){
                        $meaning = $this->EditorM->get_raw_meaning($words[$i]);
                        if($meaning->num_rows > 0) {
                            while ($row = mysqli_fetch_assoc($meaning)) {
                                $en_meaning = $this->en_stemmer($row['enUS']);
                                for($j=0; $j<count($en_words); $j++){
                                    $tEn_word = $this->en_stemmer(trim($en_words[$j]));
                                    if ($this->edit_distance(strtolower($tEn_word), strtolower(trim($en_meaning))) <= 0) {
                                        $bn_sentence = $this->highlights_fuzzy_text($bn_sentence, $words[$i]);
                                        $en_sentence = $this->highlights_fuzzy_text($en_sentence, $en_meaning);
                                        $count++;
                                    }
                                }
                            }
                        }else{
                            $root = $this->get_root($words[$i]);
                            $meaning = $this->EditorM->get_raw_meaning($root);
                            while ($row = mysqli_fetch_assoc($meaning)) {
                                $en_meaning = $this->en_stemmer($row['enUS']);
                                //echo $words[$i].'->'.$row['enUS'].'<br>';
                                for($j=0; $j<count($en_words); $j++){
                                    $tEn_word = $this->en_stemmer(trim($en_words[$j]));
                                    if ($this->edit_distance(strtolower($tEn_word), strtolower(trim($en_meaning))) <= 0) {
                                        $bn_sentence = $this->highlights_fuzzy_text($bn_sentence, $root);
                                        $en_sentence = $this->highlights_fuzzy_text($en_sentence, $en_meaning);
                                        $count++;
                                    }
                                }
                            }
                        }
                    }else{
                        $root2 = $this->get_root($word);
                        $distance = $this->edit_distance(trim($root2), trim($words[$i]));
                        if($distance <=3){
                            $meaning = $this->EditorM->get_raw_meaning($words[$i]);
                            while ($row = mysqli_fetch_assoc($meaning)) {
                                $en_meaning = $this->en_stemmer($row['enUS']);
                                //echo $words[$i].'->'.$row['enUS'].'<br>';
                                for ($j = 0; $j < count($en_words); $j++) {
                                    $tEn_word = $this->en_stemmer(trim($en_words[$j]));
                                    //echo $tEn_word.'<br>';
                                    if ($this->edit_distance( strtolower($tEn_word), strtolower(trim($en_meaning))) == 0) {
                                        $bn_sentence = $this->highlights_fuzzy_text($bn_sentence, $words[$i]);
                                        $en_sentence = $this->highlights_fuzzy_text($en_sentence, $en_meaning);
                                        $count++;
                                    }
                                }
                            }
                        }else {
                            $root = $this->get_root($words[$i]);
                            if (strlen($root) >= 6) {
                                $distance = $this->edit_distance(trim($root2), trim($root));
                                //echo $root . '<br>';
                                if ($distance <= 6) {
                                    $meaning = $this->EditorM->get_raw_meaning($root);
                                    while ($row = mysqli_fetch_assoc($meaning)) {
                                        $en_meaning = $this->en_stemmer($row['enUS']);
                                        //echo $words[$i].'->'.$row['enUS'].'<br>';
                                        for ($j = 0; $j < count($en_words); $j++) {
                                            $tEn_word = $this->en_stemmer(trim($en_words[$j]));
                                            if ($this->edit_distance(strtolower($tEn_word), strtolower(trim($en_meaning))) <= 0) {
                                                $bn_sentence = $this->highlights_fuzzy_text($bn_sentence, $root);
                                                $en_sentence = $this->highlights_fuzzy_text($en_sentence, $en_meaning);
                                                $count++;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if($count > 0){
                array_push($output_sentence_listBN, $bn_sentence);
                array_push($output_sentence_listEN, $en_sentence);
            }
        }
        //return array
        $arr = array(
            "en" => $output_sentence_listEN,
            "bn" => $output_sentence_listBN
        );
        return $arr;
    }
    function edit_distance($source, $target){
        $source_len = strlen($source);
        $target_len = strlen($target);

        for($i=0;$i<=$source_len;$i++) $d[$i][0] = $i;
        for($j=0;$j<=$target_len;$j++) $d[0][$j] = $j;

        for($i=1;$i<=$source_len;$i++) {
            for($j=1;$j<=$target_len;$j++) {
                $c = ($source[$i-1] == $target[$j-1])?0:1;
                $d[$i][$j] = min($d[$i-1][$j]+1,$d[$i][$j-1]+1,$d[$i-1][$j-1]+$c);
            }
        }

        return $d[$source_len][$target_len];
    }
    function get_TM_data($sentences){
        $output_sentence_listBN = [];
        $output_sentence_listEN = [];
        $words = [];
        $this->load->model('EditorM');
        for($i=0; $i < count($sentences); $i++){
            $word = explode(" ", trim($sentences[$i]));
            foreach ($word as $w){
                array_push($words, $w);
            }
        }
        $tm_data = $this->EditorM->get_tm_sentences();
        foreach ($tm_data->result() as $row){
            $bn_sentence = $row->sourceSentence;
            $en_sentence = $row->targetText;
            $count = 0;
            for($i=0; $i < count($words); $i++){
                if(stripos($bn_sentence, $words[$i]) !== false){
                    $meaning = $this->EditorM->get_raw_meaning($words[$i]);
                    if($meaning->num_rows > 0) {
                        while ($row = mysqli_fetch_assoc($meaning)) {
                            $en_meaning = $this->en_stemmer($row['enUS']);
                            //echo $words[$i].'->'.$row['enUS'].'<br>';
                            if (stripos($en_sentence, $en_meaning) !== false) {
                                $bn_sentence = $this->highlights_fuzzy_text($bn_sentence, $words[$i]);
                                $en_sentence = $this->highlights_fuzzy_text($en_sentence, $en_meaning);
                                $count++;
                            }
                        }
                    }else{
                        $root = $this->get_root($words[$i]);
                        $meaning = $this->EditorM->get_meaning($root);
                        while ($row = mysqli_fetch_assoc($meaning)) {
                            $en_meaning = $this->en_stemmer($row['enUS']);
                            //echo $root.'->'.$en_meaning.'<br>';
                            if (stripos($en_sentence, $en_meaning) !== false) {
                                $bn_sentence = $this->highlights_fuzzy_text($bn_sentence, $root);
                                $en_sentence = $this->highlights_fuzzy_text($en_sentence, $en_meaning);
                                $count++;
                            }
                        }
                    }
                }else{
                    $root = $this->get_root($words[$i]);
                    if(strlen($root) >=6) {
                        if (stripos($bn_sentence, $root) !== false) {
                            //root found in translated text
                            $meaning = $this->EditorM->get_meaning($root);
                            while ($row = mysqli_fetch_assoc($meaning)) {
                                $en_meaning = $this->en_stemmer($row['enUS']);
                                //echo $root.'->'.$en_meaning.'<br>';
                                if (stripos($en_sentence, $en_meaning) !== false) {
                                    $bn_sentence = $this->highlights_fuzzy_text($bn_sentence, $root);
                                    $en_sentence = $this->highlights_fuzzy_text($en_sentence, $en_meaning);
                                    $count++;
                                }
                            }
                        }
                    }
                }
            }
            if($count > 0){
                array_push($output_sentence_listBN, $bn_sentence);
                array_push($output_sentence_listEN, $en_sentence);
            }
        }
        $arr = array(
            "en" => $output_sentence_listEN,
            "bn" => $output_sentence_listBN
        );
        //print_r($arr);
        return $arr;
    }
    function en_stemmer($word){
        $list = ['ies','es','s','ed'];
        for($i=0; $i<count($list);$i++){
            if(strpos($word,$list[$i])==strlen($word)-strlen($list[$i])){
                $word =  substr($word,0,strlen($word)-strlen($list[$i]));
            }
        }
        return $word;
    }
    function highlights_fuzzy_text($en_text, $words){
        $position = stripos($en_text,$words);
        //echo $position.'<br>';
        $str1 = substr($en_text,0,$position);
        //echo $str1.'<br>';
        /*if (!preg_match('/[^A-Za-z0-9]/', $words))
        {*/
        $words = substr($en_text,$position, strlen($words));
        //}
        //echo $str2.'<br>';
        $str3 = substr($en_text,$position+strlen($words));
        //echo $str3.'<br><br><br>';
        return $str1.'<span style="color: red;">'.$words.'</span>'.$str3;
    }
    function ends_with_vowel($word){
        $vowel_list = ['অ','আ','ই','ঈ','উ','ঊ','ঋ','এ','ঐ','ও','ঔ','া','ি','ী','ু','ূ','ৃ','ে','ৈ','ো','ৌ'];
        for($i=0; $i<count($vowel_list); $i++){
            if(strpos($word,$vowel_list[$i]) == strlen($word)-strlen($vowel_list[$i])){
                return true;
            }
        }
        return false;
    }
    function get_root($word){
        //Verb Suffix List
        $verb_suffix_list = ['াইয়াছিলাম','াইয়াছিলেন','িয়েছিলেন','াইয়াছিলে','িয়েছিলাম','াইয়াছিলি','িয়েছিলে','াইতেছিলাম','াইতিছিলি','াইয়াছিল','িয়েছিলি'
            ,'াচ্ছিলাম','িয়েছিল','াইতেছিলেন','াইতেছিলে','াইতেছিল','াচ্ছিলেন','াইতেছেন','াইতেছিস','াইয়েছেন','াইয়াছেন','াইয়াছিস','াইয়েছিস','াইতেছে',
            'াইতেছ','াইতেছি','াইয়াছে','াইয়েছে','াইয়েছে','াইয়েছি','াইয়াছি','াইয়াছ','াইলেন','াইলাম','াইতেন','াইতিস','াইতাম','াচ্ছিলে','াচ্ছিলি',
            'াচ্ছিল','াইবেন','াবেন','াইবি','াইব','াইবে','াতাম','াতিস','াইতে','াতেন','াইত','ালাম','াইলি','াইলে','ালেন','াউক','াইল','াচ্ছিস',
            'াচ্ছেন','াইস','ায়','ান','াও','াস','াই','াচ্ছে','াচ্ছ','াচ্ছি','াক','ালো','ালে','ালি','াল','াতো','াতে','াত','াবে','াবি','াবো','াব',
            'িতেছিলেন','িতেছিলাম','িয়াছিলেন','িয়াছিলাম','িতেছেন','িতেছিস','িয়াছেন','িয়াছিস','িতেছিল','িতেছিলি','িয়াছিল','েছিলেন','িয়াছিলে','িয়াছিলি',
            'েছিলুম','েছিলাম','িতেছে','িতেছ','িতেছি','িয়াছে','েছেন','িয়াছ','েছিস','িয়াছি','িলেন','িলাম','িতেন','িতিস','িতাম','ছিলেন','ছিলাম','েছিল',
            'েছিলে','েছিলি','িয়াছি','িবেন','েন','িস','ছেন','চ্ছেন','চ্ছিস','ছিস','িবে','িবি','িব','িস','চ্ছিলি','বেন','িও','ছিলি','ছিল','তুম','তাম','তিস',
            'িতে','তেন','িত','লুম','লাম','িলি','িলে','লেন','িল','েছি','েছে','েছ','ুন','ুক','তো','তে','লি','লে','ল','ো','চ্ছি','চ্ছে','চ্ছ','ছি','ছে',
            'ছ','ই','াউক','াক','াইও','ায়ো','াইস','াস'];
        //,'ি'
        //Noun and Pronoun Suffix List
        $noun_suffix_list = ['দিগের','গুলির','গণের','গুলোর','দিগেতে','গুলিতে','গণে','গুলোতে','েরা','রা','গুলি','গুলো','গন','কে','দিগেরে','েরে','দের',
            'দিগকে','দিগে','ের','র','য়','েতে','তে','ে','ও'];
        //Numbers or Bachan Suffix List
        $number_suffix_list = ['গন','বর্গ','মন্ডলী','বৃন্দ','কুল','সকল','সব','সমূহ','াবলি','গুচ্ছ','দাম','মালা','নিকর','পুঞ্জ','রাজি','রাশি','যুথ'];
        for($i=0; $i<count($number_suffix_list);$i++){
            if(strlen($word) > strlen($number_suffix_list[$i])){
                if(strpos($word, $number_suffix_list[$i]) == (strlen($word)-strlen($number_suffix_list[$i]))){
                    return substr($word,0,(strlen($word)-strlen($number_suffix_list[$i])));
                }
            }
        }
        for($i=0; $i<count($noun_suffix_list);$i++){
            if(strlen($word) > strlen($noun_suffix_list[$i])){
                if(strpos($word, $noun_suffix_list[$i]) == (strlen($word)-strlen($noun_suffix_list[$i]))){
                    return substr($word,0,(strlen($word)-strlen($noun_suffix_list[$i])));
                }
            }
        }
        for($i=0; $i<count($verb_suffix_list);$i++){
            if(strlen($word) > strlen($verb_suffix_list[$i])){
                if(strpos($word, $verb_suffix_list[$i]) == (strlen($word)-strlen($verb_suffix_list[$i]))){
                    return substr($word,0,(strlen($word)-strlen($verb_suffix_list[$i])));
                }
            }
        }
        return $word;
    }
    function fuzzy_suggestion($sentence_list){
        $output_sentence_listBN = [];
        $output_sentence_listEN = [];
        for($i=0; $i< count($sentence_list); $i++){
            $words = explode(' ',trim($sentence_list[$i]));
            for($j=0; $j<count($words); $j++){
                $this->load->model('EditorM');
                $result = $this->EditorM->get_translated_sentences($words[$j]);
                if($result->num_rows() >0){
                    foreach ($result->result() as $row){
                        $bn_text = $row->sourceSentence;
                        $en_text = $row->targetText;
                        //
                        for ($k=0; $k < count($words);$k++){
                            if(stripos($bn_text,$words[$k]) !== false){
                                //Word Found in translated text
                                $meaning = $this->EditorM->get_raw_meaning($words[$k]);
                                if($meaning->num_rows > 0){
                                    while ($row = mysqli_fetch_assoc($meaning)){
                                        if(stripos($en_text, $row['enUS']) !== false){
                                            $bn_text = $this->highlights_fuzzy_text($bn_text,$words[$k]);
                                            $en_text = $this->highlights_fuzzy_text($en_text,$row['enUS']);
                                        }
                                    }
                                }else {
                                    $root = $this->get_root($words[$k]);
                                    $meaning = $this->EditorM->get_meaning($root);
                                    while ($row = mysqli_fetch_assoc($meaning)){
                                        if(stripos($en_text, $row['enUS']) !== false){
                                            $bn_text = $this->highlights_fuzzy_text($bn_text,$words[$k]);
                                            $en_text = $this->highlights_fuzzy_text($en_text,$row['enUS']);
                                        }
                                    }
                                }
                            }else{
                                $root = $this->get_root($words[$k]);
                                if(stripos($bn_text,$root) !== false){
                                    //root found in translated text
                                    $meaning = $this->EditorM->get_raw_meaning($words[$k]);
                                    while ($row = mysqli_fetch_assoc($meaning)){
                                        if(stripos($en_text, $row['enUS']) !== false){
                                            $bn_text = $this->highlights_fuzzy_text($bn_text,$root);
                                            $en_text = $this->highlights_fuzzy_text($en_text,$row['enUS']);
                                        }
                                    }
                                }
                            }
                        }
                        array_push($output_sentence_listBN, $bn_text);
                        array_push($output_sentence_listEN, $en_text);
                    }
                }else{
                    //echo 'main else';
                    $root = $this->get_root($words[$j]);
                    $result = $this->EditorM->get_translated_sentences($root);
                    foreach ($result->result() as $row){
                        $bn_text = $row->sourceSentence;
                        $en_text = $row->targetText;
                        //
                        for ($k=0; $k < count($words);$k++){
                            //echo $bn_text.'<br>';
                            if(stripos($bn_text,$words[$k]) !== false){
                                //Word Found in translated text
                                //echo $bn_text;
                                $meaning = $this->EditorM->get_raw_meaning($words[$k]);
                                if($meaning->num_rows > 0){
                                    while ($row = mysqli_fetch_assoc($meaning)){
                                        if(stripos($en_text, $row['enUS']) !== false){
                                            $bn_text = $this->highlights_fuzzy_text($bn_text,$words[$k]);
                                            $en_text = $this->highlights_fuzzy_text($en_text,$row['enUS']);
                                        }
                                    }
                                }else {
                                    $root = $this->get_root($words[$k]);
                                    $meaning = $this->EditorM->get_meaning($root);
                                    while ($row = mysqli_fetch_assoc($meaning)){
                                        if(stripos($en_text, $row['enUS']) !== false){
                                            $bn_text = $this->highlights_fuzzy_text($bn_text,$words[$k]);
                                            $en_text = $this->highlights_fuzzy_text($en_text,$row['enUS']);
                                        }
                                    }
                                }
                            }else{
                                $root = $this->get_root($words[$k]);
                                if(stripos($bn_text,$root) !== false){
                                    //echo $root.'<br>';
                                    //root found in translated text
                                    $meaning = $this->EditorM->get_raw_meaning($root);
                                    while ($row = mysqli_fetch_assoc($meaning)){
                                        $en_meaning = $this->en_stemmer($row['enUS']);
                                        //$ien_text = strtolower($en_text);
                                        if(stripos($en_text, $en_meaning) !== false){
                                            $bn_text = $this->highlights_fuzzy_text($bn_text,$root);
                                            $en_text = $this->highlights_fuzzy_text($en_text,$en_meaning);
                                        }
                                    }
                                }
                            }
                        }
                        array_push($output_sentence_listBN, $bn_text);
                        array_push($output_sentence_listEN, $en_text);
                    }
                }
            }
        }
        $arr = array(
            "en" => $output_sentence_listEN,
            "bn" => $output_sentence_listBN
        );
        //print_r($arr);
        return $arr;
    }

    function translate(){
        //print_r($_POST);
        $sentence_id = $this->input->post('sentence');
        $project_id = $this->input->post('project');
        $user_id = $this->input->post('user');
        $target_text = $this->input->post('targetText');
        $data = [
            "sentence_id" => $sentence_id,
            "project_id" => $project_id,
            "user_id" => $user_id,
            "target_text" => $target_text,
            "creation" => date("Y-m-d H:i:s",time())
        ];
        //print_r($data);
        $this->load->model('EditorM');
        if($this->input->post('skip')){
            $this->EditorM->skip_sentence($data);
            redirect(base_url().'Editor?project='.$project_id);
        }else if($this->input->post('translate')){
            $this->EditorM->translate_sentence($data);
            redirect(base_url().'Editor?project='.$project_id);
        }
    }
}