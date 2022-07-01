<style>
table {
  border-collapse: collapse;
}
th,
td {
  border: 1px solid #cecfd5;
  padding: 5px;
}
th{text-align: center;background: #000;color:#FFF;}
</style>
<table>
  <thead>
    <tr>
      <th>Módulo</th>
      <th>Curso</th>
      <th>Pregunta</th>
      <th>Respuestas</th>
      <th>Fecha</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($data as $er)
      <tr>
        <td>{{$er->curso->config->etapa}}</td>
        <td>{{$er->curso->nombre}}</td>  
        <td>{{$er->pregunta->titulo}}</td>  
        <td>{{$er->respuestas}}</td>
        <td>{{$er->created_at}}</td>
      </tr>        
    @endforeach
  </tbody>
</table>
