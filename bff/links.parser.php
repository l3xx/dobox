<?php

/*
*  Класс обработки ссылок
*/
class CLinksParser
{
    var $markExternalLinks = false;
    var $local_domains = array(); 
              
    var $addLinkFunction = false;
    var $linkFunc = 'return bfflink(this);'; 
    
    function __construct($addLinkFunction = false, $markExternalLinks = false)
    {
        $this->addLinkFunction = $addLinkFunction;    
        $this->markExternalLinks = $markExternalLinks;
    }

    function setLocalDomains($aLocalDomains = false)
    {
        $this->local_domains = (!empty($aLocalDomains) ? $aLocalDomains : array());
    }
    
    function isLocalDomain($url)
    {
        return $this->matchDomain( mb_strtolower($url), $this->local_domains );
    }       
    
    function matchDomain($url, $domains, $remove = true)
    {
        foreach ($domains as $domain)
        {
            if(strpos($url, $domain) !== false)
                return true;
        }
        return false;
    }

    /**
    * Decodes all HTML entities. The html_entity_decode() function doesn't decode numerical entities,
    * and the htmlspecialchars_decode() function only decodes the most common form for entities.
    */
    function decode_entities($text)
    {
        $text = html_entity_decode($text, ENT_QUOTES, 'ISO-8859-1');         //UTF-8 does not work!
        $text = preg_replace('/&#(\d+);/me', 'chr($1)', $text);              //decimal notation
        $text = preg_replace('/&#x([a-f0-9]+);/mei', 'chr(0x$1)', $text);    //hex notation
        $text = urldecode($text);
        return $text;
    }

    /**
    * Вставляем атрибут в HTML тег.
    */
    function insert_attribute($attr_name, $new_attr, $html_tag, $overwrite = false)
    {
        $javascript  = (strpos($attr_name, 'on') === 0); // onclick, onmouseup, onload, и т.д.
        $old_attr    = preg_replace('/^.*' . $attr_name . '="([^"]*)".*$/i', '$1', $html_tag);
        $is_attr     = !($old_attr == $html_tag);        // Атрибут уже существует?
        $old_attr    = ($is_attr) ? $old_attr : '';

        if($javascript)
        {
            if ($is_attr && !$overwrite)
            {
                //корректно закрываем существующий код
                $old_attr = ($old_attr && ($last_char = substr(trim($old_attr), -1)) && $last_char != '}' && $last_char != ';') ? $old_attr . ';' : $old_attr;

                $new_attr = $old_attr . $new_attr;
            }
            $overwrite = true;
        }

        if($overwrite && is_string($overwrite))
        {
            if(strpos(' '.$overwrite.' ', ' '.$old_attr.' ') !== false)
            {
                // Переписываем указанное значение, если уже существует, в противном случае - просто дописываем
                $new_attr = trim(str_replace(' '.$overwrite.' ', ' '.$new_attr.' ', ' '.$old_attr.' '));
            }
            else
            {
                $overwrite = false;
            }
        }
        if(!$overwrite)
        {
             // Append the new one if it's not already there.
            $new_attr = strpos(' '.$old_attr.' ', ' '.$new_attr.' ') === false ? trim($old_attr.' '.$new_attr) : $old_attr;
        }

        return ( $is_attr ? str_replace("$attr_name=\"$old_attr\"", "$attr_name=\"$new_attr\"", $html_tag) : str_replace('>', " $attr_name=\"$new_attr\">", $html_tag) );
    }

    private function hrefActivate($p)
    {
        //p[0] = url, p[1] = proto    
        $url = $this->decode_entities($p[0]);                              
        $url = str_replace('"', '&amp;', $url); // на всякий случай
        
        //truncate name
        //принцип: имя сс..ки
        $name = (strlen($url) > 55) ? mb_substr($url, 0, 39) . ' ... ' . mb_substr($url, -10) : $url;
        
        //truncate name
        //принцип: имя ссыл..
//        $length = 50;
//        if (strlen($name) > $length) {
//            $length -= 3;      
//            $name = mb_substr($name, 0, $length).'...';
//        }
       
        $url = (!empty($p[1])? $url : "http://$url"); //если протокол не указан, подставляем http://          
        $params = ($this->markExternalLinks ? (!$this->isLocalDomain($url) ? ' class="external"' : '') : '');
        $params .= ($this->addLinkFunction ? ' onclick="'.$this->linkFunc.'"' : '');
        return "<a href=\"$url\"$params>$name</a>";           
    }

    function parse($text) 
    {   
        //формируем ссылки из текста  
        $res = preg_replace_callback(
            '{    
                (?<= ^ | [\t\n\s>])           # в начале строки, после пробела, после тега
                (?:
                    ((?:http|https|ftp)://)   # протокол с двумя слэшами
                    | www\.                   # или просто начинается на www
                )
                (?> [a-z0-9_-]+ (?>\.[a-z0-9_-]+)* )   # имя хоста
                (?: : \d+)?                            # порт
                (?: &amp; | [^[\]&\s\x00»«"<>])*       # URI (но БЕЗ кавычек)
                (?:                          # последний символ должен быть...
                      (?<! [[:punct:]] )     # НЕ пунктуацией
                    | (?<= &amp; | [-/&+*] ) # но допустимо окончание на -/&+*
                )
                (?= [^<>]* (?! </a) (?: < | $)) # НЕ внутри тэга
            }xisu', 
            /* 
                так, для напоминания о флагах:
                x - whitespace data characters are totally ignored
                i - letters in the pattern match both upper and lower case letters
                s - a dot metacharacter in the pattern matches all characters
                u - pattern strings are treated as UTF-8
            */
            array(&$this, 'hrefActivate'),
            $text, -1, $cnt
        ); 
        
        $text = (!is_null($res) ? $res : $text);         

        //преобразуем ссылки
        if($cnt>0 || strpos($text, '<a ') !== false) // есть ли вообще ссылки в тексте
        {
            preg_match_all('#(<a\s[^>]+?>)(.*?</a>)#i', $text, $matches, PREG_SET_ORDER);
            foreach ($matches as $links)
            {
                $link = $new_link = $links[1];
                $href = preg_replace('/^.*href="([^"]*)".*$/i', '$1', $link);
                if($href == $link || //пустая ссылка
                   ($this->addLinkFunction && strpos($link, $this->linkFunc)!==false) ) //уже форматировали
                    continue;
                
                if($this->addLinkFunction) {
                    $new_link = $this->insert_attribute('onclick', $this->linkFunc, $new_link, false);
                    $searches[]     = $link;
                    $replacements[] = $new_link;
                }
                    
                //помечаем внешнюю ссылку
                if($this->markExternalLinks && !$this->isLocalDomain($href)) {
                    $new_link = $this->insert_attribute('class', 'external', $new_link, 'external');
                    $searches[]     = $link;
                    $replacements[] = $new_link;    
                }
            }
            
            if(isset($searches) && isset($replacements))
            {                                                              
                $text = str_replace($searches, $replacements, $text);
            }
        }
        
        return $text;
    }
    
}