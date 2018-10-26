<?php
/*
* Plugin Name: githubin
* Description: Shortcode for a box with a github project.
* Version: 1.0
* Author: laresistenciadelbit
* Author URI: https://www.laresistenciadelbit.com
*/

function githubin($atts)
{
	/* urls de prueba
	$url="https://github.com/chromium/chromium";
	$url="https://github.com/chromium/chromium/blob/master/README.md";
	$url="https://github.com/chromium/chromium?files=1";
	$url="https://github.com/chromium/chromium/blob/master/base/android/base_jni_onload.cc";
	$url="https://github.com/chromium";
	$url="https://github.com/qutebrowser/qutebrowser";
	$url="https://github.com/qutebrowser/qutebrowser/blob/master/README.asciidoc";
	$url="https://github.com/laresistenciadelbit";
	*/
	if(isset($atts["id"]) && !isset($atts["cachetime"]) )
		$atts["cachetime"]=3*3600; //(3horas por defecto)
		
	if(isset($atts["id"]) && !id_outdated_githubin($atts["id"],(int)$atts["cachetime"] ) )	//si tiene id lo leemos del contenido cacheado que hemos generado
	{
		$content_githubin=get_cached_githubin($atts["id"]);
	}
	
	
	if( !isset($content_githubin) || (isset($content_githubin) && $content_githubin=="false") )	//si no lo cogió de la cache o lo cogió pero falló, lo genera entero
	{
		if(isset($atts["url"]) && $atts["url"]!="PUT_GITHUB_URL_HERE")
			$url=$atts["url"];
		else
			return; // con plugins como elementor que previsualizan constantemente los posts, se volvía loco al previsualizar sin url el shortcode
		
		if(isset($atts["type"]))
			$type=$atts["type"];//'readme','file','folder','repos'; //OPCIONAL, se autodetecta
		
		if(isset($atts["x"]))
			$max_width=$atts["x"];
		else
			$max_width="300";
		
		if(isset($atts["y"]))
			$max_height=$atts["y"];
		else
			$max_height="300";
		
		if(isset($atts["style"]) && $atts["style"]=="box")
			$style_var="overflow-y: scroll; max-height:".$max_height."px; max-width:".$max_width."px;"; //puede ser "box" o cualquier otra cosa para no ser box
		else
			$style_var="";
		
		if(isset($atts["border"]) && ( $atts["border"]=="true" || $atts["border"]=="radius" ))
		{
			$border_var="border: 1px solid #EAEAEA;";//"true";//"true" si queremos borde.
			if($atts["border"]=="radius")
				$border_var.="border-radius: 10px;";
		}
		else
			$border_var="";
		
		if(isset($atts["fgcolor"]))
		{
			if($atts["fgcolor"]=='none')	//none es una opción para que pueda ser del mismo que tengamos ya configurado
				$fgcolor_var="";
			else
				$fgcolor_var="color:".$atts["fgcolor"].";";
		}
		else
			$fgcolor_var="";
		
		if(isset($atts["bgcolor"]))
		{
			if($atts["bgcolor"]=='none')
				$bgcolor_var="";
			else
				$bgcolor_var="background-color:".$atts["bgcolor"].";";
		}
		else
			$bgcolor_var="";
		

	//obtenemos el usuario y repositorio:
		$ruta=parse_url($url)["path"];	//devuelve solo la ruta sin dominio y sin variables

		if( strpos( $ruta, "/",2) )
		{
			$secondSlashPos=strpos( $ruta, "/",2);
			
			$gitacc=substr ( $ruta, 1,$secondSlashPos-1); //el caracter en 0 es / , no tenemos que buscarlo, por eso usamos desde el 1.
		
			if(strpos( $ruta, "/",$secondSlashPos+1))
				$current_repo=substr ( $ruta, $secondSlashPos+1 ,strpos( $ruta, "/",$secondSlashPos+1)-1-$secondSlashPos);
			else //la ruta no acaba en /, es decir, le hemos dado el repositorio sin más, lo tomaremos como que queremos ver sus ficheros
				$current_repo=substr ( $ruta, $secondSlashPos+1 );
		}
		else	//no ha encontrado / , así que tomaremos hasta el final para obtener la cuenta
		{
			$gitacc=substr ( $ruta, 1);
			$current_repo="";
		}


	//obtenemos el tipo de link para mostrar la información según sea fichero .md , fichero .algo o directorio en cualquier otro caso
		if(!isset($type))	//si no se lo hemos dado, lo busca
		{
			$posiblefile=basename($ruta);
			
			//echo $ruta.'/'.$posiblefile;die();
			
			if($ruta=='/'.$posiblefile)	//si le hemos pasado el link del usuario mostramos sus repositorios
			{
				$url="https://github.com/".$gitacc."?tab=repositories";
				$type='repos';
			}
			else
			{
				if( ($posiblefile[strlen($posiblefile)-2-1]=='.' && strtolower($posiblefile[strlen($posiblefile)-1-1])=="m" &&  strtolower($posiblefile[strlen($posiblefile)-1])=="d") || ( strlen($posiblefile)>9 && $posiblefile[strlen($posiblefile)-8-1]=='.' && $posiblefile[strlen($posiblefile)-7-1]=='a' && $posiblefile[strlen($posiblefile)-6-1]=='s' &&  $posiblefile[strlen($posiblefile)-5-1]=='c' )   )
					$type='readme';	// .md & .asciidoc
				else
				{
					if(strstr($posiblefile,'.'))//if($posiblefile[strlen($posiblefile)-3-1]=='.') <-las extensiones de los ficheros pueden ser de 1 a 4 o 5 caracteres
						$type='file';
					else
					{
						$type='folder';
						if($url=="https://github.com/".$gitacc."/".$current_repo )	
							$url="https://github.com/".$gitacc."/".$current_repo."?files=1";
					}
				}
			}
		}


	//obtenemos el contenido
		
		$useragent = "Mozilla/5.0 (Linux; Android 4.4.".rand(1,4)."; C2105 Build/15.3.A.1.14) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.93 Mobile Safari/537.36";

		 $ch = curl_init($url);
		 curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
		 curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		 curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
		 curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		 $content_githubin = curl_exec($ch);
		 curl_close($ch);

			//si usamos simple_html_dom_1.5:
		 //$html = str_get_html($content_githubin);
		 //echo'<!-- '.$html->find('article').'-->';
		 //echo'<!-- '.$html->find('.classid').'-->';
		
		
	 //limpiamos el header
		 switch($type)
		 {
			case 'readme':
				$content_githubin=preg_replace('/<!DOCTYPE[\s\S]*?<article/imu','<article',$content_githubin);
			 break;
			 case 'folder':
				$content_githubin=preg_replace('/<!DOCTYPE[\s\S]*?<div class=\"list files-list\"/imu','<div class="list files-list"',$content_githubin);
				$content_githubin=preg_replace('/<\/div>[\s]*<footer class=/imu','<footer class',$content_githubin);	//quitamos el último </div> ya que empezamos desde el segundo div no desde el primero.
			 break;
			 case 'file':
				$content_githubin=preg_replace('/<!DOCTYPE[\s\S]*?<pre>/imu','<div id="github_file">',$content_githubin);
				$content_githubin=preg_replace('/<\/pre>[\s\S]*<footer class/imu','</div> <footer class',$content_githubin);
			 break;
			 case 'repos':
				$content_githubin=preg_replace('/<!DOCTYPE[\s\S]*?<div class=\"list repo-list/imu','<div class="list repo-list',$content_githubin);
			 break;
		 }
		 
	 //limpiamos el footer
		$content_githubin=preg_replace('/<footer class[\s\S]*<\/html>/imu','',$content_githubin);
		
		
	 //quitamos las imágenes (si así se ha querido)
		if( isset($atts["disable_images"]) && $atts["disable_images"]!='false' )
			$content_githubin=preg_replace('/<img [\s\S]*?>/imu','',$content_githubin);

	 //quitamos fav del repositorio
		if( isset($atts["disable_fav"]) && $atts["disable_fav"]!='false')
			$content_githubin=preg_replace('/\<p class=\"text\-gray text\-small mb\-0 mt\-2[\s\S]*?<\/p>/imu','',$content_githubin);
			
	 //quitamos los css	(ya las hemos quitado en el header)
		//$content_githubin=preg_replace('/<link .+?\>/imu','',$content_githubin);
		
	 //convertimos los h1 y h2 y h3 en <b> para controlar el color en temas oscuros:
		$content_githubin=preg_replace('/<h[1-3]>/imu','<b>',$content_githubin);
		$content_githubin=preg_replace('/<\/h[1-3]>/imu','</b>',$content_githubin);
		
	 //reemplazamos links del repositorio con la ruta absoluta:
		$replaceto='href="https://github.com/'.$gitacc.'/';
		$content_githubin=preg_replace('/href="\/'.$gitacc.'\//imu',$replaceto,$content_githubin);
		
	 //reemplazamos links de imágenes del repositorio con la ruta absoluta:
		$replaceto='<img src="https://github.com/'.$gitacc.'/';
		$content_githubin=preg_replace('/\<img src="\/'.$gitacc.'\//imu',$replaceto,$content_githubin);

	 //evitamos mas cajas de [github_box] dentro
		$content_githubin=preg_replace('/\[github_box/imu','<span>[</span>github_box',$content_githubin);
		$content_githubin=preg_replace('/\&\#91\;github_box/imu','<span>[</span>github_box',$content_githubin);
		
		
		$content_githubin='<div style=" '.$style_var.$bgcolor_var.$fgcolor_var.$border_var.'
		 padding:8px;">'.$content_githubin.'</div>';
		 
	 //si tiene id lo cacheamos (lo metemos en un fichero con su id con formato <githubin_ID>
		if(isset($atts["id"]) && $atts["id"]!="" )
		{	
			$myfile = fopen(plugin_dir_path( __FILE__ )."githubin_".$atts["id"], "w") or die("Unable to open file!");
			fwrite($myfile, $final_box);
			fclose($myfile);
		}
	}
	echo $content_githubin;
}
// \s match any kind of invisible character & \S match any kind of visible character
// [\s\S]* <-look until the last match ; [\s\S]*? <- look until the first match
// https://regex101.com/

add_shortcode('github_box', 'githubin');


function id_outdated_githubin($id,$cachetime)
{
	if( file_exists(plugin_dir_path( __FILE__ )."githubin_".$id) && time() - filemtime(plugin_dir_path( __FILE__ )."githubin_".$id) < $cachetime )
		return false;
	else
		return true;	
}

function get_cached_githubin($id)
{	
	$myfile = fopen(plugin_dir_path( __FILE__ )."githubin_".$id, "r");
	if(!$myfile) 
		return "false";
	$content_githubin=fread($myfile,filesize(plugin_dir_path( __FILE__ )."githubin_".$id));
	fclose($myfile);
	return($content_githubin);
}


function github_box_button_script() 
{
    if(wp_script_is("quicktags"))
    {
        ?>
            <script type="text/javascript">
                //this function is used to retrieve the selected text from the text editor
                /*function getSel()
                {
                    var txtarea = document.getElementById("content");
                    var start = txtarea.selectionStart;
                    var finish = txtarea.selectionEnd;
                    return txtarea.value.substring(start, finish);
                }*/
                QTags.addButton( 
					"github_box_shortcode",//"code_shortcode",
					"github_box", 
                    callback
                );
                function callback()
                {
                    //var selected_text = getSel();
                    QTags.insertContent('[github_box url="PUT_GITHUB_URL_HERE" border="radius" style="box" x="300" y="300" fgcolor="#333" bgcolor="#fafafa" disable_images="false"]');
                }
            </script>
        <?php
    }
}
add_action("admin_print_footer_scripts", "github_box_button_script");

function github_content_button_script() 
{
    if(wp_script_is("quicktags"))
    {
        ?>
            <script type="text/javascript">
                QTags.addButton( 
					"github_content_shortcode",//"code_shortcode",
					"github_content", 
                    callback
                );
                function callback()
                {
                    QTags.insertContent('[github_box url="PUT_GITHUB_URL_HERE" border="false" style="none" fgcolor="none" bgcolor="none" disable_images="false"]');
                }
            </script>
        <?php
    }
}
add_action("admin_print_footer_scripts", "github_content_button_script");

?>