<?php
/*
* Plugin Name: embed-githubin
* Description: Shortcode for a box with a github project.
* Version: 1.13
* Author: laresistenciadelbit
* Author URI: https://laresistenciadelbit.baal.host
*/
function githubin($atts)
{
	/* urls de prueba
	$url="https://github.com/chromium/chromium";
	$url="https://github.com/chromium/chromium/blob/master/README.md";
	$url="https://github.com/chromium/chromium?files=1";
	$url="https://github.com/chromium/chromium/blob/master/base/android/base_jni_onload.cc";
	$url="https://github.com/chromium";
	$url="https://github.com/laresistenciadelbit";
	*/
	if(isset($atts["id"]) && !isset($atts["cachetime"]) )
		$atts["cachetime"]=1*3600; //(1hora por defecto de cacheo)
	
	$myserverip='1.1.1.1'; //si quieres recachear con una tarea cron y evitar el tiempo definido de cacheo, pon aquí la ip del servidor
	if( isset($atts["id"]) && !id_outdated_githubin($atts["id"],(int)$atts["cachetime"] ) && $_SERVER['REMOTE_ADDR']!=$myserverip )	//si tiene id lo leemos del contenido cacheado que hemos generado , solo si no es myserverip
	{	//cron recaching if server ip:  */15 * * * * curl https://url
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
			$border_var="border: 1px solid #363a3d;";//"true";//"true" si queremos borde.
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
		
		//$useragent = "Mozilla/5.0 (Linux; Android 4.4.".rand(1,4)."; C2105 Build/15.3.A.1.14) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.93 Mobile Safari/537.36"; //android 4.4.*
		$useragent = "Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A5370a Safari/604.1"; //iphone os11
		
		//using wordpress request internal API (https://developer.wordpress.org/reference/classes/WP_Http/request/)
		$args_rr = array('user-agent'=> apply_filters( 'http_headers_useragent', $useragent , $url ));

		$resp = wp_remote_request( $url, $args_rr );
		$content_githubin=$resp['body'];

	 //limpiamos el header
		 switch($type)
		 {
			case 'readme':
				$content_githubin=preg_replace('/<!DOCTYPE[\s\S]*?<article/imu','<article',$content_githubin);
			 break;
			 case 'folder':	//actualizado 2021/05/10
				$content_githubin="<style>.Box-row{display:flex;}</style>".$content_githubin;//añadimos estilo
				
				$content_githubin=preg_replace('/<!DOCTYPE[\s\S]*?class="Box-row/imu','<div class="Box-row',$content_githubin);
				//$content_githubin=preg_replace('/<div role="columnheader"[\s\S]*?<\/div>/imu','',$content_githubin); //quitamos columnas absurdas
				//$content_githubin=preg_replace('/<button[\s\S]*?<\/button>/imu','',$content_githubin); //quitamos botones absurdos
				//$content_githubin=preg_replace('/<\/div>[\s]*<\/div>[\s]*<\/div>[\s]*<\/div>[\s]*<\/main>/imu','</main>',$content_githubin);//cambiamos </div></div></div></div></main> por </main> (por si no tuviese readme quitamos esos primero)
				//$content_githubin=preg_replace('/<div id="readme"[\s\S]*<\/main>/imu','<div></main>',$content_githubin);//(si tiene readme) desde readme hasta el </main> (metemos un <div> para arreglar un </div> solitario
				//$content_githubin=preg_replace('/<\/div>[\s]*<\/include-fragment>/imu','</div>',$content_githubin); //quitamos un include-fragment que sobraba
				$content_githubin=preg_replace('/<time-ago[\s\S]*?<\/time-ago>/imu','',$content_githubin); //quitamos los time-ago
				$content_githubin=preg_replace('/<a data-pjax=[\s\S]*?<\/a>/imu','',$content_githubin); //quitamos los <a data-pjax
				$content_githubin=preg_replace('/<a style=\"opacity:0;\"[\s\S]*?<\/a>/imu','',$content_githubin);//quitamos contenido invisible (que ocupa espacio)
				$content_githubin=preg_replace('/<\/turbo-frame>/imu','',$content_githubin);//desde </main> hasta el fin
				$content_githubin=preg_replace('/<\/main>[\s\S]*/imu','',$content_githubin);//desde </main> hasta el fin
				
			 break;
			 case 'file':
				$content_githubin=preg_replace('/<!DOCTYPE[\s\S]*?<table/imu','<table id="github_file" style="white-space: pre;"',$content_githubin);
				$content_githubin=preg_replace('/<\/table[\s\S]*div class=\"footer/imu','</table><div class="footer',$content_githubin);
				//$content_githubin=preg_replace('/<!DOCTYPE[\s\S]*?<pre>/imu','<div id="github_file">',$content_githubin);
				//$content_githubin=preg_replace('/<\/pre>[\s\S]*<footer class/imu','</div> <footer class',$content_githubin);
			 break;
			 case 'repos':
				//$content_githubin=preg_replace('/<!DOCTYPE[\s\S]*?<div class=\"list repo-list/imu','<div class="list repo-list',$content_githubin);
				$content_githubin=preg_replace('/<!DOCTYPE[\s\S]*?<div id=\"user-repositories-list/imu','<div id="user-repositories-list',$content_githubin);
				$content_githubin=preg_replace('/<!DOCTYPE[\s\S]*?<div class=\"org-repos repo-list/imu','<div class="org-repos repo-list',$content_githubin);
				//quitamos todos los nuevos span que han añadido (2021-03-01)
				while( preg_match( '/<span[\s\S]*?<\/span>/imu', $content_githubin) )
					$content_githubin=preg_replace('/<span[\s\S]*?<\/span>/imu','',$content_githubin);
				while( preg_match( '/<\/span>/imu', $content_githubin) )
					$content_githubin=preg_replace('/<\/span>/imu','',$content_githubin);
				while( preg_match( '/Updated <relative-time[\s\S]*?<\/relative-time>/imu', $content_githubin) )
					$content_githubin=preg_replace('/Updated <relative-time[\s\S]*?<\/relative-time>/imu','',$content_githubin);
				//quitamos los poll-include-fragment para que no tenga links rotos internamente (2021-05-02)
				while( preg_match( '/<poll-include-fragment[\s\S]*?<\/poll-include-fragment>/imu', $content_githubin) )
					$content_githubin=preg_replace('/<poll-include-fragment[\s\S]*?<\/poll-include-fragment>/imu','',$content_githubin);
			 break;
		 }

	 //limpiamos el footer

		if($type=='readme')
			$content_githubin=preg_replace('/<\/article>[\s\S]*<\/html>/imu','</article>',$content_githubin);
		else
		{
			if($type=='repos')
			{
				//$content_githubin=preg_replace('/<div class=(?!.[\s\S]*<div class=)[\s\S]*js-profile-tab-count-container[\s\S]*<\/html>/imu','',$content_githubin); //en los repositorios de organizaciones también hay más contenido que sobra ; forzamos a que coja el último match con -> MATCH(?!.[\s\S]*MATCH)  donde match es el último atributo a buscar
				$content_githubin=preg_replace('/<\/ul>[\s\S]*<\/html>/imu','</ul></div>',$content_githubin);
			}
			else
			{
				$content_githubin=preg_replace('/<div class=\"footer [\s\S]*<\/html>/imu','',$content_githubin); //ahora han cambiado en según que páginas footer por div class="footer
				//$content_githubin=preg_replace('/<\/table>[\s\S]*<\/html>/imu','',$content_githubin);//para ficheros quitamos desde que finaliza la tabla de contenido </table> hasta el final
				$content_githubin=preg_replace('/<footer class[\s\S]*<\/html>/imu','',$content_githubin);
			}
		}

		if($type=='repos') //limpiamos el nuevo contenido que han añadido
		{
			//limpiamos bloques de licencias
			$content_githubin=preg_replace('/<div class="f6 text-gray mt-2">[\s\S]*?<\/div>/imu','',$content_githubin);
			$content_githubin=preg_replace('/<div class="text-gray f6 mt-2">[\s\S]*?<\/div>/imu','',$content_githubin);
			
		}

		if($type=='file')
		{	//css de enlightment de github:
			$content_githubin='
				<style>
				/*!
				 * GitHub Light v0.5.0
				 * Copyright (c) 2012 - 2017 GitHub, Inc.
				 * Licensed under MIT (https://github.com/primer/github-syntax-theme-generator/blob/master/LICENSE)
				 */
				.pl-c /* comment, punctuation.definition.comment, string.comment */ {color: #6a737d;}
				.pl-c1 /* constant, entity.name.constant, variable.other.constant, variable.language, support, meta.property-name, support.constant, support.variable, meta.module-reference, markup.raw, meta.diff.header, meta.output */,
				.pl-s .pl-v /* string variable */ {color: #005cc5;}
				.pl-e /* entity */,
				.pl-en /* entity.name */ { color: #6f42c1;}
				.pl-smi /* variable.parameter.function, storage.modifier.package, storage.modifier.import, storage.type.java, variable.other */,
				.pl-s .pl-s1 /* string source */ {color: #24292e;}
				.pl-ent /* entity.name.tag, markup.quote */ {color: #22863a;}
				.pl-k /* keyword, storage, storage.type */ {color: #d73a49;}
				.pl-s /* string */,
				.pl-pds /* punctuation.definition.string, source.regexp, string.regexp.character-class */,
				.pl-s .pl-pse .pl-s1 /* string punctuation.section.embedded source */,
				.pl-sr /* string.regexp */,
				.pl-sr .pl-cce /* string.regexp constant.character.escape */,
				.pl-sr .pl-sre /* string.regexp source.ruby.embedded */,
				.pl-sr .pl-sra /* string.regexp string.regexp.arbitrary-repitition */ {color: #032f62;}
				.pl-v /* variable */,
				.pl-smw /* sublimelinter.mark.warning */ {color: #e36209;}
				.pl-bu /* invalid.broken, invalid.deprecated, invalid.unimplemented, message.error, brackethighlighter.unmatched, sublimelinter.mark.error */ {color: #b31d28;}
				.pl-ii /* invalid.illegal */ {color: #fafbfc;background-color: #b31d28;}
				.pl-c2 /* carriage-return */ {color: #fafbfc;background-color: #d73a49;}
				.pl-c2::before /* carriage-return */ {content: "^M";}
				.pl-sr .pl-cce /* string.regexp constant.character.escape */ {font-weight: bold;color: #22863a;}
				.pl-ml /* markup.list */ {color: #735c0f;}
				.pl-mh /* markup.heading */,
				.pl-mh .pl-en /* markup.heading entity.name */,
				.pl-ms /* meta.separator */ {font-weight: bold;color: #005cc5;}
				.pl-mi /* markup.italic */ {font-style: italic;color: #24292e;}
				.pl-mb /* markup.bold */ {font-weight: bold;color: #24292e;}
				.pl-md /* markup.deleted, meta.diff.header.from-file, punctuation.definition.deleted */ {color: #b31d28;background-color: #ffeef0;}
				.pl-mi1 /* markup.inserted, meta.diff.header.to-file, punctuation.definition.inserted */ {color: #22863a;background-color: #f0fff4;}
				.pl-mc /* markup.changed, punctuation.definition.changed */ {color: #e36209;background-color: #ffebda;}
				.pl-mi2 /* markup.ignored, markup.untracked */ {color: #f6f8fa;background-color: #005cc5;}
				.pl-mdr /* meta.diff.range */ {font-weight: bold;color: #6f42c1;}
				.pl-ba /* brackethighlighter.tag, brackethighlighter.curly, brackethighlighter.round, brackethighlighter.square, brackethighlighter.angle, brackethighlighter.quote */ {color: #586069;}
				.pl-sg /* sublimelinter.gutter-mark */ {color: #959da5;}
				.pl-corl /* constant.other.reference.link, string.other.link */ {text-decoration: underline;color: #032f62;}
				</style>'.$content_githubin;

			//$content_githubin='<style>.blob-num:before {content: attr(data-line-number); color: rgba(27,31,35,.3);} .blob-code{border:1px solid transparent;}</style>'.$content_githubin;
			$content_githubin='<style>.blob-num {border:1px solid transparent; padding:0px;} .blob-code{border:1px solid transparent;}</style>'.$content_githubin;
		}
		
	 //quitamos las imágenes (si así se ha querido)
		if( isset($atts["disable_images"]) && $atts["disable_images"]!='false' )
			$content_githubin=preg_replace('/<img [\s\S]*?>/imu','',$content_githubin);

	 //quitamos fav del repositorio
		if( isset($atts["disable_fav"]) && $atts["disable_fav"]!='false')
			$content_githubin=preg_replace('/\<p class=\"text\-gray text\-small mb\-0 mt\-2[\s\S]*?<\/p>/imu','',$content_githubin);
			
	 //quitamos los css	(ya las hemos quitado en el header)
		//$content_githubin=preg_replace('/<link .+?\>/imu','',$content_githubin);
		
	 //convertimos los h1 y h2 y h3 en <b> para controlar el color en temas oscuros y reducir el tamaño:
		if($type=='repos') //si es repositorio le damos estilo (ya que nos lo han cambiado todo)
		{
			$content_githubin=preg_replace('/<h[1-3].*?>/imu','
<p style="padding-top:4px;margin-bottom:0;">
<svg class="octicon octicon-repo" viewBox="0 0 12 16" version="1.1" width="12" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M4 9H3V8h1v1zm0-3H3v1h1V6zm0-2H3v1h1V4zm0-2H3v1h1V2zm8-1v12c0 .55-.45 1-1 1H6v2l-1.5-1.5L3 16v-2H1c-.55 0-1-.45-1-1V1c0-.55.45-1 1-1h10c.55 0 1 .45 1 1zm-1 10H1v2h2v-1h3v1h5v-2zm0-10H2v9h9V1z"></path></svg>
			',$content_githubin);
		}
		else
			$content_githubin=preg_replace('/<h[1-3].*?>/imu','<p>',$content_githubin);
		$content_githubin=preg_replace('/<\/h[1-3]?>/imu','</p>',$content_githubin);
		
	 //reemplazamos links del repositorio con la ruta absoluta:
		$replaceto='href="https://github.com/'.$gitacc.'/';
		$content_githubin=preg_replace('/href="\/'.$gitacc.'\//imu',$replaceto,$content_githubin);
		
	 //reemplazamos links de imágenes del repositorio con la ruta absoluta:
		$replaceto='<img src="https://github.com/'.$gitacc.'/';
		$content_githubin=preg_replace('/\<img src="\/'.$gitacc.'\//imu',$replaceto,$content_githubin);

	 //evitamos mas cajas de [github_box] dentro
		$content_githubin=preg_replace('/\[github_box/imu','<span>[</span>github_box',$content_githubin);
		$content_githubin=preg_replace('/\&\#91\;github_box/imu','<span>[</span>github_box',$content_githubin);
		
	 //arreglamos cierre de divs
		$opened_divs=substr_count($content_githubin,'<div');
		$closed_divs=substr_count($content_githubin,'</div>');
		for($i=0; $i<($closed_divs - $opened_divs); $i++)
			$content_githubin=str_lreplace('</div>','',$content_githubin);
		
	 //lo englobamos en su div correspondiente con la configuración elegida
		$content_githubin='<div class="embed_github" style=" '.$style_var.$bgcolor_var.$fgcolor_var.$border_var.'
		 padding:8px;">'.$content_githubin.'</div>';
		 
	 //si tiene id lo cacheamos (lo metemos en un fichero con su id con formato <githubin_ID>
		if(isset($atts["id"]) && $atts["id"]!="" )
		{	
			$myfile = fopen(plugin_dir_path( __FILE__ )."githubin_".$atts["id"], "w") or die("Unable to open file!");
			fwrite($myfile, $content_githubin);
			fclose($myfile);
		}
	}
	return $content_githubin;
}
// \s match any kind of invisible character & \S match any kind of visible character
// [\s\S]* <-look until the last match ; [\s\S]*? <- look until the first match
// https://regex101.com/

add_shortcode('github_box', 'githubin');

function str_lreplace($search, $replace, $subject)
{
    $pos = strrpos($subject, $search);
    if($pos !== false)
        $subject = substr_replace($subject, $replace, $pos, strlen($search));
    return $subject;
}

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
	if(!$myfile || filesize(plugin_dir_path( __FILE__ )."githubin_".$id)==0) 
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