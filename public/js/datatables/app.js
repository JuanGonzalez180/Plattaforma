$(document).ready( function () {
    $('#myTable').DataTable({
        "lengthMenu": [
            [20, 50, 100],
            [20, 50, 100]
        ],
        "language": {
            "lengthMenu": "Mostrar _MENU_ registros por pagina",
            "zeroRecords": "Nothing found - sorry",
            "info": "Mostrando la pagina _PAGE_ de _PAGES_",
            "infoEmpty": "No hay elementos",
            "infoFiltered": "(filtrado de _MAX_ registros totales)",
            "search": "Buscar:",
            "paginate":{
                "next":"Siguiente",
                "previous":"Anterior"
            }
        }
    });
} );