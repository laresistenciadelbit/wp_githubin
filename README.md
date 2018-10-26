## githubin

![Alt repo_widget.png](wp_screenshots/repo_widget.png?raw=true "repo_widget.png")

ENGLISH
-------

Put github content in your wordpress site easily.
Either in a post or a widget you can get a file list, repositories or a single file from github.

This plugin creates a shortcode and two buttons in the post editor (text section)

![Alt post_editor.png](wp_screenshots/post_editor.png?raw=true "post_editor.png")

For better embed visualization it automatically gets the mobile version content of github.

The buttons just print the shortcode in the editor in this format:

>[github_box url="PUT_GITHUB_URL_HERE" border="radius" style="box" x="300" y="300" fgcolor="#333" bgcolor="#fafafa" disable_images="false"]

>[github_box url="PUT_GITHUB_URL_HERE" border="false" style="none" fgcolor="none" bgcolor="none" disable_images="false"]

The only difference between one and the other is that the first (github_box) 
put the github content into a scrollable box and the github_content button leave the content
without the box.


The variables:

> url="PUT_GITHUB_URL_HERE"

	Here you can put either a github user url, a repository url, a readme url or a file url.
	They are threated in different ways; user url will show its repositories;
	repository url will show its main files, readme and files will show its contents.
	

> border="radius" OR border="true"
	
	If we use one of this options will create a border with the content.

> style="box"

	It will create a scrollable box with the content
	
> x="300" y="300"

	Those options are the dimensions of the box
	
> fgcolor="#333" bgcolor="#fafafa"

	Set the text color and background color
	
> disable_images="true"

	Remove all images from the content
	
> disable_fav="true"
	
	Remove the fav parragraphs from an user repositories
	
> id="IDNAME"
	
	Creates a cached file of the content. This setting is very recomended to use.
	But remember: if you change later the attributes of the shortcode it won't update
	until the cachetime has passed or unless you delete the cached file (in plugin's directory).
	
> cachetime="10800"	

	Number of seconds before it rebuilds the cache file (default is 10800seconds=3hours)
	

ESPAÑOL
-------

Pon contenido de github en tu wordpress de manera sencilla.
Ya sea en un post o en un widget puedes obtener un listado de ficheros, repositorios,
o un fichero concreto de github.

Este plugin crea un shortcode y dos botones en el editor de posts (sección de texto)
![Alt post_editor.png](wp_screenshots/post_editor.png?raw=true "post_editor.png")

Para mejor visualización del contenido el plugin automáticamente toma la versión
del contenido móvil de github.

Los botones del editor simplemente ponen el shortcode en el editor en este formato:

>[github_box url="PUT_GITHUB_URL_HERE" border="radius" style="box" x="300" y="300" fgcolor="#333" bgcolor="#fafafa" disable_images="false"]

>[github_box url="PUT_GITHUB_URL_HERE" border="false" style="none" fgcolor="none" bgcolor="none" disable_images="false"]

La única diferencia entre uno y otro es que el primero (github_box)
pone el contenido en una caja desplegable (scroll) y el botón github_content
deja el contenido tal como es.



Las variables:

> url="PUT_GITHUB_URL_HERE"

	Aquí puedes poner una url de usuario de github, la de un repositorio,
	la de un readme.md / readme.asciidoc , o la de un fichero
	
	Estos son tratados de manera distinta para filtrar su contenido
	
	La url de usuario mostrará sus repositorios y la de un repositorio sus ficheros
	

> border="radius" OR border="true"
	
	Si usamos una de estas opciones nos creará un borde con el contenido

> style="box"
	
	Creará una caja deslizable con el contenido
	
> x="300" y="300"

	Estas opciones son las dimensiones de la caja
	
> fgcolor="#333" bgcolor="#fafafa"

	Cambia el color de la letra o del fondo
	
> disable_images="true"

	Quita todas las imágenes del contenido
	
> disable_fav="true"
	
	Quita los párrafos de favoritos de los repositorios de un usuario
	
> id="IDNAME"
	
	Crea un fichero caché con el contenido. Es muy recomendado el uso de esta opción.
	Pero recuerda: si después cambias los atributos del shortcode no se actualizarán
	hasta que haya pasado el tiempo de cache "cachetime" o hayas eliminado el fichero caché (en el directorio del plugin)

> cachetime="10800"	

	Número de segundos antes de que se regenere el fichero caché de nuevo. (por defecto es 10800segundos=3horas)

	