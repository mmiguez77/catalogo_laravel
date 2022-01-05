<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Marca;
use App\Models\Categoria;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    
    public function portada()
    {
        $productos = Producto::with([ 'getMarca', 'getCategoria' ])->paginate(5);
        return view('portada', ['productos' => $productos]);

    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $productos = Producto::with([ 'getMarca', 'getCategoria' ])->paginate(5);
        return view('adminProductos', ['productos' => $productos]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $marcas = Marca::all();
        $categorias = Categoria::all();
        return view('agregarProducto', 
            [
                'marcas' => $marcas,
                'categorias' => $categorias
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // validarForm
        $this->validarForm($request);

        //subir img
        $prdImagen = $this->subirImagen($request);

        //instanciamos
        $Producto = new Producto();

        //asignamos atributos
        $Producto->prdNombre = $request->prdNombre;
        $Producto->prdPrecio = $request->prdPrecio;
        $Producto->idMarca = $request->idMarca;
        $Producto->idCategoria = $request->idCategoria;
        $Producto->prdPresentacion = $request->prdPresentacion;
        $Producto->prdStock = $request->prdStock;
        $Producto->prdImagen = $prdImagen;

        //guardamos
        $Producto->save();

        //redireccion
        return redirect('adminProductos')
            ->with(['mensaje' => 'Producto: '.$request->prdNombre.' agregado']);

    }

    private function subirImagen(Request $request) {

            //si no se envia la imagen
            $prdImagen = 'noDisponible.jpg';

            //sólo en formulario de modificar
            if( $request->has('imgActual') ){
                $prdImagen = $request->imgActual;
            } 

            //si se envia la imagen
            if ( $request->file('prdImagen') ) {

            //renombrar
            $extension = $request->file('prdImagen')->extension();
            $prdImagen = time().'.'.$extension;

            //subir archivo
            $request->file('prdImagen')
                ->move(public_path('productos/'),$prdImagen);

        }

        return $prdImagen;
    }
    

    private function validarForm(Request $request) {
        $request->validate(
            [
                'prdNombre'=>'required|min:2|max:50',
                'prdPrecio'=>'required|numeric|min:0',
                'idMarca' =>'required',
                'idCategoria' =>'required',
                'prdPresentacion' =>'required|min:3|max:150',
                'prdStock' =>'required|integer|min:0',
                'prdImagen' =>'mimes:jpg,jpeg,png,gif,svg,webp|max:1024'
            ],
            [
                'prdNombre.required'=>'El campo "Nombre del producto" es obligatorio.',
                'prdNombre.min'=>'El campo "Nombre del producto" debe tener como mínimo 2 caractéres.',
                'prdNombre.max'=>'El campo "Nombre" debe tener 30 caractéres como máximo.',
                'prdPrecio.required'=>'Complete el campo Precio.',
                'prdPrecio.numeric'=>'Complete el campo Precio con un número.',
                'prdPrecio.min'=>'Complete el campo Precio con un número positivo.',
                'idMarca.required'=>'Seleccione una marca.',
                'idCategoria.required'=>'Seleccione una categoría.',
                'prdPresentacion.required'=>'Complete el campo Presentación.',
                'prdPresentacion.min'=>'Complete el campo Presentación con al menos 3 caractéres',
                'prdPresentacion.max'=>'Complete el campo Presentación con 150 caractérescomo máxino.',
                'prdStock.required'=>'Complete el campo Stock.',
                'prdStock.integer'=>'Complete el campo Stock con un número entero.',
                'prdStock.min'=>'Complete el campo Stock con un número positivo.',
                'prdImagen.mimes'=>'Debe ser una imagen.',
                'prdImagen.max'=>'Debe ser una imagen de 2MB como máximo.'
            ]
        );

    }
    
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Producto  $producto
     * @return \Illuminate\Http\Response
     */

    public function show(Producto $producto)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Producto  $producto
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $Producto = Producto::find($id);
        $marcas = Marca::all();
        $categorias = Categoria::all();

        return view('modificarProducto', 
            [   
                'Producto' => $Producto,
                'marcas' => $marcas,
                'categorias' => $categorias
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Producto  $producto
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Producto $producto)
    {
        // validarForm
        $this->validarForm($request);

        // subir Imagen
        $prdImagen = $this->subirImagen($request);

        // obtener datos de un producto por ID
        $Producto = Producto::find($request->idProducto);

        // modificar atributos
        $Producto->prdNombre = $request->prdNombre;
        $Producto->prdPrecio = $request->prdPrecio;
        $Producto->idMarca = $request->idMarca;
        $Producto->idCategoria = $request->idCategoria;
        $Producto->prdPresentacion = $request->prdPresentacion;
        $Producto->prdStock = $request->prdStock;
        $Producto->prdImagen = $prdImagen;

        // guardar un producto
        $Producto->save();
        
        // redireccion
        return redirect('adminProductos')
            ->with(['mensaje' => 'Producto: '.$request->prdNombre.' modificado']);

    }


    public function confirmarBaja($id) 
    {   
        // obtener datos de una marca por ID
        $Producto = Producto::with([ 'getMarca', 'getCategoria' ])->find($id);
     
        // caso que no tenga productos la marca se puede borrar
        return view('/eliminarProducto', [ 'Producto' => $Producto]);
               
        // redireccion caso que no se pueda borrar porque tiene productos la marca
        return redirect('/adminMarcas') 
            ->with([ 
                'mensaje' => 'La marca ' . $Producto->prdNombre . ' no se puede eliminar', 
                'alert' => 'danger' 
            ]); 
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Producto  $producto
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        Producto::destroy($request->idProducto); 

        return redirect('/adminProductos')
            ->with([
                    'mensaje' => 'Producto: ' .$request->prdNombre. ' eliminado correctamente',
                    'alert' => 'info'
            ]);

    }
}
