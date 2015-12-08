<?php

// convert nouns in a text into other nouns
//

$nouns_file = "nouns-working.txt";
$names_bible_file = "names-bible.txt";
$names_popular_file = "names-popular.txt";

$nouns = file($nouns_file, FILE_IGNORE_NEW_LINES);
$names_bible = file($names_bible_file, FILE_IGNORE_NEW_LINES);
$names_popular = file($names_popular_file, FILE_IGNORE_NEW_LINES);

$subs = array();
$offset = 7;

for($x=0;$x<count($nouns);$x++){
    $subs[strtolower($nouns[$x])] = strtolower($nouns[$x+$offset]);
}

// open the text to be translated
$files = glob('src/*.txt');
foreach($files as $file) {
    echo "file: ".$file."\n";
    $file_contents = '';
    $dir = str_replace("src", "final", substr($file, 0, strpos($file, "Chapter") -1));
    $dest_file = $dir."/".substr($file, strpos($file, "/") + 1);
    echo "dir: ".$dir."\n";
    echo "dest file: ".$dest_file."\n";
    if(!file_exists("./".$dir)){
        echo "making directory: |{$dir}|\n";
        mkdir("./".$dir);
    }
    // if($count++ ==10) exit(0);

    $src_file = fopen($file, "r");
    while(!feof($src_file)){
        $output = '';
        $line = fgets($src_file);
        if(preg_match("/Title: /", $line)){
            $file_contents .= $line;
            continue;
        }
        # do same stuff with the $line
        $words = split(" ", $line);
        //print_r($words);
        foreach($words as $word){
            $clean_word = strtolower(preg_replace('/[^a-zA-Z]s?/', '', $word));
            if(ctype_upper(substr($word, 0, 1)))
                $capitalise = true;
            else
                $capitalise = false;
            // debug("dirty: ".$word.", clean: ".$clean_word);
            if(in_array($clean_word, array_values($nouns))){
                // debug($clean_word." is in list ");
                if($capitalise)
                    $output .= str_ireplace($clean_word, ucfirst($subs[$clean_word]), $word)." ";
                else
                    $output .= str_ireplace($clean_word, $subs[$clean_word], $word)." ";
    
            }elseif(substr($clean_word, -1) == 's' && in_array(rtrim($clean_word, 's'), array_values($nouns))){
                // is the $word a plural of a $clean_word that exists
                $output .= str_ireplace(rtrim($clean_word, 's'), $subs[rtrim($clean_word, 's')], $word)." ";
    
            }elseif(in_array($clean_word, array_values($names_bible))){
                // is the $word a name
                //echo "$word -> ".$names_popular[array_search($clean_word, $names_bible)]."\n";
                $output .= str_ireplace($clean_word, ucfirst($names_popular[array_search($clean_word, $names_bible)]), $word)." ";
    
            }else{
                // debug($clean_word." not in list ");
                $output .= rtrim($word)." ";
            }
    
        }
        //echo $line;
        $file_contents .= trim($output)."\n";
    }
    file_put_contents($dest_file, $file_contents);
}

function debug($str){
    echo "\n".$str."\n";
}

?>
