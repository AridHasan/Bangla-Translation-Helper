<?php

class Editor extends CI_Controller
{
    /*
     * This the Editor Class where user will translate sentences.
     * In this page, user will get Glossary suggestion and Translation Memory matching suggestions.
     */
    public function index(){
        $this->load->model('Auth');
        //checking the user either logged in or not
        if($this->Auth->islogged() == true){
            $uId = $_SESSION['uId'];//getting user id from session data
            if(array_key_exists('project',$_GET)) {
                $pId = $_GET['project'];//getting project id from url
                $this->load->model('EditorM');
                /*
                 * Getting the all the log of a specific user
                 * 1. all the sentences of selected project
                 * 2. all the translated sentences of the selected project
                 * 3. all the skipped sentences by the user of this selected project
                 */
                $response = $this->EditorM->get_sentences($uId,$pId);
                $sentences = $response['sentence'];
                $skips = $response['skip'];
                $translated = $response['translated'];
                $skip = [];
                //list of skipping sentence by the user of this project
                if(!empty($skips)) {
                    foreach ($skips->result() as $row) {
                        array_push($skip, $row->sId);
                    }
                }
                $translate = [];
                //list of translated sentences of this project(translated by every user)
                if(!empty($translated)) {
                    foreach ($translated->result() as $row) {
                        array_push($translate, $row->sId);
                    }
                }
                $data=[];
                //Selecting the sentence which will be translated by the user
                foreach ($sentences->result() as $row){
                    if(!in_array($row->sId, $skip) and !in_array($row->sId, $translate)){
                        $data['sId'] = $row->sId;
                        $data['pId'] = $row->projectId;
                        $data['uId'] = $uId;
                        $data['source'] = $row->sourceSentence;
                        break;
                    }
                }
                //if there is no available sentence to translate then if the user is a project admin then he will redirect to project settings
                // to upload sentences
                // or the user is a translator or expert translator then the user will redirect to dashboard.
                if(empty($data['source'])){
                    $this->session->set_flashdata('message','At first, Please Upload text file to translate');
                    redirect(base_url()."ProjectSettings?project=".$pId);
                }
                /*
                 * Now rest of the code will show the glossary and Translation Memory suggestion
                 */
                $sentence = str_replace(',','',$data['source']);
                //split a line into multiple sentences if there is any sentence break presents
                $sentence_list = explode('।', $sentence);
                $glossary = [];
                // Glossary Suggestion
                for($i=0; $i<count($sentence_list);$i++){
                    //extract words from sentences
                    $words = explode(' ',trim($sentence_list[$i]));
                    for($j=0; $j<count($words);$j++){
                        $output = '';
                        //try to getting of the word
                        $meaning = $this->EditorM->get_raw_meaning($words[$j]);
                        if($meaning->num_rows > 0){
                            //if the word meaning exists in database then we will add the meaning in our glossary suggestion
                            while ($row = mysqli_fetch_assoc($meaning)){
                                extract($row);
                                $output .= $enUS.", ";
                            }
                            $output = trim($output);
                            $output = substr($output,0,strlen($output)-strlen(','));
                            $arr = array($words[$j], $output);
                            array_push($glossary,$arr);
                        }else {
                            //if the word meaning isn't presents in database the we will strip suffix for getting root word
                            $root = $this->get_root($words[$j]);
                            //checking either the root word ends with a vowel or consonant
                            $end_vowel = $this->ends_with_vowel($root);
                            // adding া for the word which will ends with consonant
                            if($end_vowel == false) {
                                $root2 = $root . 'া';
                            }else{
                                $root2= $root;
                            }
                            // then again try to getting the meaning of the new root word
                            $meaning = $this->EditorM->get_raw_meaning($root2);
                            if($meaning->num_rows > 0){
                                // if this time word meaning presents in database the system will add this meaning to glossary
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
                                // if the word meaning doesn't presents in database
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
                                    //trying to remove inflation of Bengali verb
                                    $root_ = substr($root,0,6);
                                    $root_ = str_replace('ে','া',$root_);//remove exceptional verb inflation
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
                //getting translated sentences
                $tm_data = $this->EditorM->get_tm_sentences();
                //Approximate String Search for fuzzy matching
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
    /*Approximate String Search algorithm
     * title={An Edit-Distance Model for the Approximate Matching of Timed Strings},
      author={Simon Dobrišek , Janez Žibert , Nikola Pavešić , France Mihelič },
      booktitle={IEEE Transactions on Pattern Analysis & Machine Intelligence, vol. 31},
      pages={736-741},
      Issue No={04 - April (2009 vol. 31)},
      organization={IEEE}
     */
    function approximate_matching($trans_sent, $target_sent){
        $output_sentence_listBN = [];
        $output_sentence_listEN = [];
        $words = [];
        $this->load->model('EditorM');
        //Extract words from given target sentences which is known as source sentences
        for($i=0; $i < count($target_sent); $i++){
            $word = explode(" ", trim($target_sent[$i]));
            foreach ($word as $w){
                array_push($words, $w);
            }
        }
        // try to extract each sentences from whole translated sentence
        foreach ($trans_sent->result() as $row){
            $count = 0;
            $bn_sentence = $row->sourceSentence;
            $en_sentence = $row->targetText;
            $match_word_bn = [];
            $match_word_en = [];
            //Split english sentences into words
            $trans_words = explode(' ', trim($bn_sentence));
            //Split english sentences into words
            $en_words = explode(' ', str_replace('.','',trim($en_sentence)));
            for($i=0; $i<count($words); $i++){
                foreach ($trans_words as $word){
                    //getting edit-distance of two bengali words
                    $distance = $this->edit_distance(trim($word), trim($words[$i]));
                    if($distance <= 0){
                        //if distance is less than 0 that means the words are equal
                        //Now try to get the meaning of the word
                        $meaning = $this->EditorM->get_raw_meaning($words[$i]);
                        if($meaning->num_rows > 0) {
                            while ($row = mysqli_fetch_assoc($meaning)) {
                                //suffix striping for english word
                                $en_meaning = $this->en_stemmer($row['enUS']);
                                for($j=0; $j<count($en_words); $j++){
                                    $tEn_word = $this->en_stemmer(trim($en_words[$j]));
                                    //Now matching two english word for translation memory suggestion
                                    if ($this->edit_distance(strtolower($tEn_word), strtolower(trim($en_meaning))) == 0) {
                                        array_push($match_word_bn, $words[$i]);
                                        array_push($match_word_en, $en_meaning);
                                        //$bn_sentence = $this->highlights_fuzzy_text($bn_sentence, $words[$i]);
                                        //$en_sentence = $this->highlights_fuzzy_text($en_sentence, $en_meaning);
                                        $count++;
                                    }
                                }
                            }
                        }else{
                            // if the word meaning not found in database then we need to strip suffix for getting meaning
                            $root = $this->get_root($words[$i]);
                            $meaning = $this->EditorM->get_raw_meaning($root);
                            while ($row = mysqli_fetch_assoc($meaning)) {
                                //suffix striping for english word
                                $en_meaning = $this->en_stemmer($row['enUS']);
                                //echo $words[$i].'->'.$row['enUS'].'<br>';
                                for($j=0; $j<count($en_words); $j++){
                                    $tEn_word = $this->en_stemmer(trim($en_words[$j]));
                                    //Now matching two english word for translation memory suggestion
                                    if ($this->edit_distance(strtolower($tEn_word), strtolower(trim($en_meaning))) == 0) {
                                        array_push($match_word_bn, $root);
                                        array_push($match_word_en, $en_meaning);
                                        //$bn_sentence = $this->highlights_fuzzy_text($bn_sentence, $root);
                                        //$en_sentence = $this->highlights_fuzzy_text($en_sentence, $en_meaning);
                                        $count++;
                                    }
                                }
                            }
                        }
                    }else{
                        //suffix striping for translated bengali word
                        $root2 = $this->get_root($word);
                        $distance = $this->edit_distance(trim($root2), trim($words[$i]));
                        if($distance <=0){
                            $meaning = $this->EditorM->get_raw_meaning($words[$i]);
                            while ($row = mysqli_fetch_assoc($meaning)) {
                                $en_meaning = $this->en_stemmer($row['enUS']);
                                //echo $words[$i].'->'.$row['enUS'].'<br>';
                                for ($j = 0; $j < count($en_words); $j++) {
                                    $tEn_word = $this->en_stemmer(trim($en_words[$j]));
                                    //echo $tEn_word.'<br>';
                                    if ($this->edit_distance( strtolower($tEn_word), strtolower(trim($en_meaning))) == 0) {
                                        array_push($match_word_bn, $words[$i]);
                                        array_push($match_word_en, $en_meaning);
                                        //$bn_sentence = $this->highlights_fuzzy_text($bn_sentence, $words[$i]);
                                        //$en_sentence = $this->highlights_fuzzy_text($en_sentence, $en_meaning);
                                        $count++;
                                    }
                                }
                            }
                        }else {
                            //suffix striping for target bengali word
                            $root = $this->get_root($words[$i]);
                            if (strlen($root) >= 6) {
                                $distance = $this->edit_distance(trim($root2), trim($root));
                                //echo $root . '<br>';
                                if ($distance <= 0) {
                                    $meaning = $this->EditorM->get_raw_meaning($root);
                                    while ($row = mysqli_fetch_assoc($meaning)) {
                                        $en_meaning = $this->en_stemmer($row['enUS']);
                                        //echo $words[$i].'->'.$row['enUS'].'<br>';
                                        for ($j = 0; $j < count($en_words); $j++) {
                                            $tEn_word = $this->en_stemmer(trim($en_words[$j]));
                                            if ($this->edit_distance(strtolower($tEn_word), strtolower(trim($en_meaning))) == 0) {
                                                array_push($match_word_bn, $root);
                                                array_push($match_word_en, $en_meaning);
                                                //$bn_sentence = $this->highlights_fuzzy_text($bn_sentence, $root);
                                                //$en_sentence = $this->highlights_fuzzy_text($en_sentence, $en_meaning);
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
            //If any words matches between translated sentence and target sentence the translated sentence will be added into Translation Memory
            if($count > 0 and count($match_word_bn) > 0 and count($match_word_en) >0){
                $bn_sentence = $this->highlights_text($bn_sentence, $match_word_bn);
                $en_sentence = $this->highlights_text($en_sentence, $match_word_en);
                array_push($output_sentence_listBN, $bn_sentence);
                array_push($output_sentence_listEN, $en_sentence);
                $count = 0;
            }
            unset($match_word_en);unset($match_word_bn);
        }
        //return array
        $arr = array(
            "en" => $output_sentence_listEN,
            "bn" => $output_sentence_listBN
        );
        return $arr;
    }
    //Highlights Matching words for Approximate String Search Algorithm
    function highlights_text($sentence, $words){
        foreach($words as $word){
            if(stripos($sentence, $word) !== false){
                $pos = stripos($sentence,$word);
                $str1 = substr($sentence,0,$pos);
                $str2 = substr($sentence,$pos, strlen($word));
                $str3 = substr($sentence,$pos+strlen($word));
                $sentence = $str1."<span style='color: red;'>".$str2."</span>".$str3;
            }
        }
        return $sentence;
    }
    //Edit distance calculation using Levenshtein Distance Algorithm
    //https://en.wikibooks.org/wiki/Algorithm_Implementation/Strings/Levenshtein_distance
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
    /*
     * This is another approach for showing translation memory suggestion.
     * In this function, no referenced algorithm used.
     */
    function get_TM_data($sentences){
        $output_sentence_listBN = [];
        $output_sentence_listEN = [];
        $words = [];
        $this->load->model('EditorM');
        //Transform source sentence into words
        for($i=0; $i < count($sentences); $i++){
            $word = explode(" ", trim($sentences[$i]));
            foreach ($word as $w){
                array_push($words, $w);
            }
        }
        //Getting translated sentences from database
        $tm_data = $this->EditorM->get_tm_sentences();
        foreach ($tm_data->result() as $row){
            //Bengali Sentence
            $bn_sentence = $row->sourceSentence;
            //Translated English sentence
            $en_sentence = $row->targetText;
            $count = 0;
            for($i=0; $i < count($words); $i++){
                if(stripos($bn_sentence, $words[$i]) !== false){
                    //if source word contains in translated sentence then system will try to getting the meaning of the source word
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
                        //if source word not founds in database then system need to remove suffix from the word
                        $root = $this->get_root($words[$i]);
                        //again try to get meaning of the root word
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
                    //if the source word doesn't contains in translated sentence then try to get the root word from source word
                    //then try to find that the translated sentence contains the root word or not
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
    //A simple English stemmer which will remove the suffix from English word
    function en_stemmer($word){
        $list = ['ies','es','s','ed'];
        for($i=0; $i<count($list);$i++){
            if(strpos($word,$list[$i])==strlen($word)-strlen($list[$i])){
                $word =  substr($word,0,strlen($word)-strlen($list[$i]));
            }
        }
        return $word;
    }
    //This function is used when system use get_TM_data($sentences) function
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
    //checking a bengali word that the word either ends with vowel or not
    function ends_with_vowel($word){
        $vowel_list = ['অ','আ','ই','ঈ','উ','ঊ','ঋ','এ','ঐ','ও','ঔ','া','ি','ী','ু','ূ','ৃ','ে','ৈ','ো','ৌ'];
        for($i=0; $i<count($vowel_list); $i++){
            if(strpos($word,$vowel_list[$i]) == strlen($word)-strlen($vowel_list[$i])){
                return true;
            }
        }
        return false;
    }
    //Try to getting the root word/ strip suffix/ remove suffix from a bengali word
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
    /*
     * this is another method to show fuzzy matching but it isn't efficient too much.
     * That's why we remove this method from our application

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
    }*/

    /*
     * When a user will click translate or skip button then this function will work
     */
    function translate(){
        //print_r($_POST);
        //Getting required data from html form
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
            //if skip button is clicked
            $this->EditorM->skip_sentence($data);
            redirect(base_url().'Editor?project='.$project_id);
        }else if($this->input->post('translate')){
            //if translate button is clicked
            $this->EditorM->translate_sentence($data);
            redirect(base_url().'Editor?project='.$project_id);
        }
    }
}