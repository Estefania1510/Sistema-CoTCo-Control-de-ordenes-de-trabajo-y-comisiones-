$(document).ready(function () {

 
  $('.tabla-responsiva').each(function () {
    $(this).DataTable({
      responsive: {
        details: {
          type: 'column',
          target: 0
        }
      },
      columnDefs: [{
        className: 'dtr-control',
        orderable: false,
        targets: 0
      }],
      order: [1, 'asc'],
      paging: false,      
      searching: false,   
      info: false,        
      autoWidth: false,   
      language: {
        emptyTable: "No hay datos para mostrar",
        infoEmpty: "Sin registros disponibles"
      }
    });
  });

});
