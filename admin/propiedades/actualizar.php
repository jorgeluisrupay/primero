<?php 

    require '../../includes/funciones.php';  
    $auth = estadoAutenticado();
    
    if(!$auth){
        header('Location: /');
    }


    //Validar un id valido
    $id = $_GET['id'];
    $id = filter_var($id, FILTER_VALIDATE_INT); //validar el get k sea int

    if(!$id){
        header('Location: /admin');
    }
    
    //var_dump($id);

    //base de datos
    require '../../includes/config/database.php';
    $db = conectarBD();

    //Obtener los datos de la propiedad
    $consulta = "SELECT * FROM propiedades WHERE id = ${id}";
    $resultado = mysqli_query($db,$consulta);
    $propiedad = mysqli_fetch_assoc($resultado);


    //consulta para obtener los vendedores
    $consulta = "SELECT * FROM vendedores";
    $resultado = mysqli_query($db, $consulta);


    //Arreglo con mensajes de errores
    $errores = [];
        //Para caundo se equivoca de llenar no empieze de nuevo
        //Para k al actualizar el formulario este lleno
        $titulo = $propiedad['titulo'];
        $precio = $propiedad['precio'];
        $descripcion = $propiedad['descripcion'];
        $habitaciones = $propiedad['habitaciones'];
        $wc = $propiedad['wc'];
        $estacionamiento = $propiedad['estacionamiento'];
        $vendedorId = $propiedad['vendedorId'];
        $imagenPropiedad =$propiedad['imagen'];

    //Ejecutar el codigo despues de que el usuario envie el formulario
    if($_SERVER['REQUEST_METHOD']==='POST'){

       /* echo "<pre>";
        var_dump($_POST);
        echo "</pre>"; */

        //PARA LEER IMAGENES
        /*echo "<pre>";
        var_dump($_FILES);
        echo "</pre>"; */
       
        


        $titulo = mysqli_real_escape_string($db, $_POST['titulo']);
        $precio = mysqli_real_escape_string($db, $_POST['precio']);
        $descripcion = mysqli_real_escape_string($db, $_POST['descripcion']);
        $habitaciones = mysqli_real_escape_string($db, $_POST['habitaciones']);
        $wc = mysqli_real_escape_string($db, $_POST['wc']);
        $estacionamiento = mysqli_real_escape_string($db, $_POST['estacionamiento']);
        $vendedorId = mysqli_real_escape_string($db, $_POST['vendedor']);
        $creado = date('Y/m/d');


        //Asignar imagenes
        $imagen = $_FILES['imagen'];
        var_dump($imagen['name']);


        if(!$titulo){
            $errores[] = "Debes añadir un titulo";
        }

        if(!$precio){
            $errores[] = "El precio es obligatorio";
        }
        if( strlen($descripcion) < 20){
            $errores[] = "La descripcion es obligatorio debe tener 20 caracteres";
        }
        if(!$habitaciones){
            $errores[] = "El numero de habitaciones es obligatorio";
        }

        if(!$wc){
            $errores[] = "El numero de baños es obligatorio";
        }
        if(!$estacionamiento){
            $errores[] = "El numero de lugares de estacionamiento es obligatorio";
        }
        if(!$vendedorId){
            $errores[] = "Elige un vendedor";
        }
       /* if(!$imagen['name'] || $imagen['error']){
            $errores[] = "La imagen es obligatoria";
        }*/

        //Validar por tamaño(100kb maximo)
        $medida = 1000*1000;
        if($imagen['size']>$medida){
            $errores[] = 'La imagen es muy pesada';
        }
        /*
        echo "<pre>";
        var_dump($errores);
        echo "</pre>"; */
        
        //Revisar que el array de errores este vacio
        //Empty revisa k este vacio el array y isset k exista
        if(empty($errores)){
            
            //Crear una carpeta
            $carpetaImagenes = '../../imagenes';
             //Subida de archivos 
            
            //si no existe la carpeta se cre una
            if(!is_dir($carpetaImagenes)){
                mkdir($carpetaImagenes);
            }

            $nombreImagen = '';

            //Para eliminar de la carpeta para ahorrar memoria
            if($imagen['name']){
                // echo "Si hay una nueva imagen";
                //Eliminar la imagen previa
 
                unlink($carpetaImagenes . $propiedad['imagen']);
                //Generar un nombre unico
            $nombreImagen = md5(uniqid(rand(),TRUE)).".jpg"; 
            //var_dump($nombreImagen);

            //subir imagen
            move_uploaded_file($imagen['tmp_name'], $carpetaImagenes . "/". $nombreImagen );
             }else{
                 $nombreImagen = $propiedad['imagen'];
             }

            
            
            
             //Insertar la actualizacion en la base de datos
        $query = "UPDATE propiedades set titulo= '${titulo}', precio='${precio}',imagen='${nombreImagen}'
        ,descripcion='${descripcion}', habitaciones=${habitaciones},wc=${wc}
        ,estacionamiento=${estacionamiento},vendedorId=${vendedorId} where id =${id}";

        //echo $query;

        $resultado = mysqli_query($db,$query);

        if($resultado){
            //Redireccionar al usuario
            header('Location: /admin?resultado=2');

            //echo "Insertado Correctamente";
        }

        }

    }
    
    


      
    incluirTemplate('header'); 
?>

    <main class="contenedor seccion">
        <h1>Actualizar Propiedad</h1>

        <a href="/admin" class="boton boton-verde">Volver</a>

        <?php foreach($errores as $error): ?>
            <div class="alerta error">
            <?php echo $error ?>
            </div>

            
        <?php endforeach; ?>
                

        <form class="formulario" method="POST"  enctype="multipart/form-data">
            <fieldset>
                <legend>Informacion General</legend>

                <label for="titulo">Titulo: </label>
                <input type="text" id="titulo" name="titulo" placeholder="Titulo Propidedad" value="<?php echo $titulo ?>">

                <label for="precio">Precio: </label>
                <input type="number" id="precio" name="precio" placeholder="Precio Propidedad" value="<?php echo $precio ?>">

                <label for="imagen">Imagen: </label>
                <input type="file" id="imagen" accept="image/jpeg, image/png" name="imagen">

                <img src="/imagenes/<?php echo $imagenPropiedad; ?>" class="imagen-small">

                <label for="descripcion">Descripcion: </label>
                <textarea id="descripcion" name="descripcion"><?php echo $descripcion; ?></textarea>

            </fieldset>

                
            <fieldset>
                <legend>Informacion Propiedad</legend>
                
                <label for="habitaciones">Habitaciones: </label>
                <input type="number" id="habitaciones" name="habitaciones" placeholder="Ej: 3" min="1" max="9" value="<?php echo $habitaciones ?>">

                <label for="wc">Baños: </label>
                <input type="number" id="wc" name="wc" placeholder="Ej: 3" min="1" max="9" value="<?php echo $wc ?>">

                <label for="estacionamiento">Estacionamiento: </label>
                <input type="number" id="estacionamiento" name="estacionamiento" placeholder="Ej: 3" min="1" max="9" value="<?php echo $estacionamiento ?>">

            </fieldset>

            <fieldset>
                <legend>Vendedor</legend>

                <select name="vendedor">
                    <option value="">-- Seleccione --</option>
                    <?php while($vendedor = mysqli_fetch_assoc($resultado)):  ?>
                        <option  value="<?php echo $vendedor['id'];?>"> <?php echo $vendedor['nombre']." ".$vendedor['apellido']; ?></option>
                    <?php endwhile; ?>
                </select>
            </fieldset>

            <input type="submit" value="Actualizar Propiedad" class="boton boton-verde">

        </form>
    </main>
    
    <?php 
        incluirTemplate('footer');
    ?>