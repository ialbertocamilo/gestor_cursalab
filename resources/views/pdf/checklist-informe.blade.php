<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
</head>

<body>
    <div class="container">
        <div class="p-6">
            <h2 class="text-2xl font-bold mb-4">Datos generales del checklist</h2>
            <div class="grid gap-4" style="grid-template-columns: 8fr 4fr">
                <div class="flex flex-col">
                    <div style="border: 1px solid yellow;" class="my-2">
                        <div class="bg-white px-4 py-4 my-0 border-l-4 border-yellow-400 shadow-sm">
                            <div class="flex justify-between">
                                <span>Auditoría de control de calidad</span>
                                <span>Resultado: 95/100</span>
                            </div>
                        </div>
                    </div>
                    <div style="border: 1px solid red;" class="my-2">
                        <div class="bg-white px-4 py-4 my-0 border-l-4 border-red-400 shadow-sm">
                            <div class="flex justify-between">
                                <span>Auditoría de Servicio del cliente</span>
                                <span>Resultado: 97/100</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-4 flex items-center shadow-md">
                    <div class="" style="width: 100%;height: 100%;">
                        <span class="">Resultado general:</span>
                        <div class="text-4xl font-bold text-green-500 text-center mt-10">96<span
                                class="text-xl">/100</span></div>
                    </div>
                </div>
            </div>
            <div class="flex flex-col mt-6">
                <div class="bg-white p-4 shadow-md">
                    <h3 class="text-xl font-bold mb-2">Resultado comparativo</h3>
                    <p class=" mb-4">Periodo 03/2024 - 05/2024</p>
                    <canvas id="myChart" width="w-full" height="h-auto"></canvas>
                </div>
                <div class="bg-white p-4 shadow-md">
                    <div class="flex justify-start items-center mb-2">
                        <h3 class="text-xl font-bold">Comentarios</h3>
                        <span class="ml-4"> - Con inteligencia artificial</span>
                    </div>
                    <div class="text-sm">
                        <div class="flex items-center mb-2">
                            <span class="inline-block w-3 h-3 bg-red-500 rounded-full mr-2"></span>
                            <span>Auditoría de control de calidad</span>
                        </div>
                        <ul class="list-disc list-inside  mb-4">
                            <li>Comienza con un puntaje alto, pero disminuye abruptamente en el tercer día.</li>
                            <li>Presenta fluctuaciones en los puntajes a lo largo del tiempo.</li>
                            <li>Puede haber inconsistencias que necesitan ser abordadas.</li>
                        </ul>
                        <div class="flex items-center mb-2">
                            <span class="inline-block w-3 h-3 bg-yellow-400 rounded-full mr-2"></span>
                            <span>Auditoría de Servicio del cliente</span>
                        </div>
                        <ul class="list-disc list-inside ">
                            <li>
                                Comienza con puntajes bajos, pero mejora gradualmente y se mantiene estable en niveles
                                más altos.
                            </li>
                            <li>
                                Puede haber habido problemas iniciales en el servicio al cliente que se han abordado y
                                mejorado con el tiempo.
                            </li>
                            <li>La consistencia en los puntajes es generalmente alta.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <html>

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <script src="https://cdn.tailwindcss.com"></script>
        </head>

        <body>
            <div class="p-4">
                <h2 class="text-xl font-semibold mb-4">Resultado de las temáticas</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white p-4 rounded-lg shadow-md">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium">Calidad de servicio</h3>
                            <span class="text-2xl font-bold">98</span>
                        </div>
                        <div class="flex justify-center w-full mb-4">
                            <div style="height: 200px;width: 200px">
                                <canvas id="donut" width="250" height="250"></canvas>
                            </div>
                        </div>
                        <div class="flex justify-around text-sm">
                            <div class="flex items-center">
                                <span class="w-3 h-3 bg-green-500 rounded-full inline-block mr-2"></span> Positivo 80%
                            </div>
                            <div class="flex items-center">
                                <span class="w-3 h-3 bg-yellow-500 rounded-full inline-block mr-2"></span> Medio 10%
                            </div>
                            <div class="flex items-center">
                                <span class="w-3 h-3 bg-orange-500 rounded-full inline-block mr-2"></span> Negativo 10%
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow-md">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium">Higiene y seguridad</h3>
                            <span class="text-2xl font-bold">80</span>
                        </div>
                        <div class="flex justify-center w-full mb-4">
                            <div style="height: 200px;width: 200px">
                                <canvas id="donut2" width="250" height="250"></canvas>
                            </div>
                        </div>
                        <div class="flex justify-around text-sm">
                            <div class="flex items-center">
                                <span class="w-3 h-3 bg-green-500 rounded-full inline-block mr-2"></span> Positivo 80%
                            </div>
                            <div class="flex items-center">
                                <span class="w-3 h-3 bg-yellow-500 rounded-full inline-block mr-2"></span> Medio 10%
                            </div>
                            <div class="flex items-center">
                                <span class="w-3 h-3 bg-orange-500 rounded-full inline-block mr-2"></span> Negativo 10%
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow-md">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium">Servicio al cliente</h3>
                            <span class="text-2xl font-bold">99</span>
                        </div>
                        <div class="flex justify-center w-full mb-4">
                            <div style="height: 200px;width: 200px">
                                <canvas id="donut3" width="250" height="250"></canvas>
                            </div>
                        </div>
                        <div class="flex justify-around text-sm">
                            <div class="flex items-center">
                                <span class="w-3 h-3 bg-green-500 rounded-full inline-block mr-2"></span> Positivo 80%
                            </div>
                            <div class="flex items-center">
                                <span class="w-3 h-3 bg-yellow-500 rounded-full inline-block mr-2"></span> Medio 10%
                            </div>
                            <div class="flex items-center">
                                <span class="w-3 h-3 bg-orange-500 rounded-full inline-block mr-2"></span> Negativo 10%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </body>

        </html>
    </div>
    <div class="container">
        <div class="p-4">
            <h2 class="text-xl font-bold mb-4">Datos granulares de los checklist</h2>
            <div class="overflow-x-auto">
                <table class="border min-w-full bg-white rounded-lg">
                    <thead>
                        <tr style="background: #A9B2B9; color:white;">
                            <th colspan="3" class="py-2 px-4 text-left">Nombre del checklist</th>
                            <th colspan="3" class="py-2 px-4 text-left">Área auditada</th>
                            <th colspan="3" class="py-2 px-4 text-left">Temática</th>
                            <th colspan="3" class="py-2 px-4 text-left">Actividades</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="row">
                            <td colspan="3" rowspan="2" class="border p-2">
                                <span class="color-checklist" style="background: red;"></span> Auditoría de Control de
                                Calidad
                            </td>
                            <td colspan="3" rowspan="2" class="border p-2">
                                Zona de Preparación de Alimentos
                            </td>
                            <td colspan="3" rowspan="1" class="border p-2">
                                Control de Calidad
                            </td>
                            <td colspan="3" rowspan="1" class="border p-2">
                                <ul class="list-disc list-inside">
                                    <li>Inspeccionar la calidad y frescura de los ingredientes utilizados.</li>
                                    <li>Evaluar el sabor, presentación y temperatura de los platos servidos.</li>
                                    <li>Revisar el cumplimiento de las recetas y estándares de la cadena.</li>
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" rowspan="1" class="border p-2">Higiene y Seguridad</td>
                            <td colspan="3" rowspan="1" class="border p-2">
                                <ul class="list-disc list-inside">
                                    <li>Inspeccionar la limpieza y desinfección de las áreas de cocina.</li>
                                    <li>Evaluar el cumplimiento de las normas de seguridad alimentaria.</li>
                                    <li>Revisar los protocolos de emergencia y evacuación.</li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="row">
                            <td colspan="3" class="border p-2">
                                <span class="color-checklist" style="background: green;"></span>Auditoría de
                                Procedimientos de Cocina
                            </td>
                            <td colspan="3" class="border p-2">
                                Cocina Principal
                            </td>
                            <td colspan="3" class="border p-2">
                                Procedimientos de Cocina
                            </td>
                            <td colspan="3" class="border p-2">
                                <ul class="list-disc list-inside">
                                    <li>Observar los procesos de preparación y cocción para asegurar que sigan los
                                        protocolos establecidos.</li>
                                    <li>Verificar el uso correcto de utensilios y equipos de cocina.</li>
                                    <li>Evaluar el tiempo de preparación y servicio de los alimentos.</li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="row">
                            <td colspan="3" class="border p-2">
                                <span class="color-checklist" style="background: purple;"></span> Auditoría de
                                Servicio al Cliente
                            </td>
                            <td colspan="3" class="border p-2">
                                Área de Comedor
                            </td>
                            <td colspan="3" class="border p-2">
                                Servicio al Cliente
                            </td>
                            <td colspan="3" class="border p-2">
                                <ul class="list-disc list-inside">
                                    <li>Revisar el trato del personal hacia los clientes, incluyendo cortesía y
                                        eficiencia.</li>
                                    <li>Evaluar el tiempo de espera desde la entrada hasta el servicio de la comida.
                                    </li>
                                    <li>Comprobar la gestión de quejas y sugerencias de los clientes.</li>
                                </ul>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="p-6">
            <h1 class="text-2xl font-bold">Resumen temática : Auditoria control de
                calidad
                <span class="text-sm font-normal">(Recurrente)</span>
            </h1>
            <h2 class="text-lg font-semibold mt-4">Insights del los auditores (con inteligencia artificial) <span
                    class="text-sm font-normal"></span></h2>
            <div class="mt-6 space-y-4">
                <div class="bg-white p-4 rounded-lg shadow-md flex items-start grid"
                    style="grid-template-columns: 1fr 2fr 1fr 8fr">
                    <div class="h-full flex justify-center items-center">
                        <div class="w-2 bg-blue-500 mr-4" style="width: 20px;height:80px ;"></div>
                    </div>
                    <div class="flex-grow">
                        <h3 class="text-lg font-bold">Auditor</h3>
                        <p class="text-xl font-bold">José Benítez</p>
                        <p class="text-sm">Actividades realizadas: 10/10</p>
                        <p class="text-sm">Puntuación de checklist: 16.5</p>
                    </div>
                    <div class="h-full flex flex-col justify-center items-center">
                        <div class="h-full border"></div>
                        <img src="https://placehold.co/32x32" alt="icon" class="w-8 h-8">
                        <div class="h-full border"></div>
                    </div>
                    <div class="ml-4 border-zinc-300 dark:border-zinc-700 pl-4">
                        <ul class="list-disc list-inside">
                            <li>A1 (Inspeccionar la calidad y frescura de los ingredientes utilizados): Se muestra un
                                puntaje elevado de
                                15, lo que sugiere un alto cumplimiento en la inspección de la calidad de los
                                ingredientes. Cumple con los
                                estándares establecidos.</li>
                            <li>A2 (Evaluar el sabor, presentación y temperatura de los platos servidos): Se registra un
                                puntaje más
                                alto de 18, indicando un excelente desempeño en la presentación, sabor y temperatura de
                                los platos. Cumple
                                con los estándares establecidos.</li>
                            <li>A3 (Revisar el cumplimiento de las recetas y estándares de la cadena): Se mantiene un
                                buen nivel de
                                cumplimiento con un puntaje de 16.5, lo que sugiere que se están cumpliendo los
                                estándares establecidos.
                                Cumple con los estándares establecidos.</li>
                        </ul>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-md flex items-start grid"
                    style="grid-template-columns: 1fr 2fr 1fr 8fr">
                    <div class="h-full flex justify-center items-center">
                        <div class="w-2 bg-purple-500 mr-4" style="width: 20px;height:80px ;"></div>
                    </div>
                    <div class="flex-grow">
                        <h3 class="text-lg font-bold">Auditor</h3>
                        <p class="text-xl font-bold">Mario Rodriguez</p>
                        <p class="text-sm">Actividades realizadas: 10/10</p>
                        <p class="text-sm">Puntuación de checklist: 16.5</p>
                    </div>
                    <div class="h-full flex flex-col justify-center items-center">
                        <div class="h-full border"></div>
                        <img src="https://placehold.co/32x32" alt="icon" class="w-8 h-8">
                        <div class="h-full border"></div>
                    </div>
                    <div class="ml-4 border-zinc-300 dark:border-zinc-700 pl-4">
                        <ul class="list-disc list-inside">
                            <li>A1 (Inspeccionar la calidad y frescura de los ingredientes utilizados): Se observa un
                                nivel moderado de
                                cumplimiento, con un puntaje de 8. Está en proceso de mejora.</li>
                            <li>A2 (Evaluar el sabor, presentación y temperatura de los platos servidos): Se registra un
                                buen desempeño,
                                con un puntaje de 12. Está en proceso de mejora.</li>
                            <li>A3 (Revisar el cumplimiento de las recetas y estándares de la cadena): Se evidencia un
                                alto nivel de
                                cumplimiento, con un puntaje de 15. Cumple con los estándares establecidos.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="p-4 bg-white rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4">Resultado comparativo de los
                auditores segun
                actividad</h2>
            <div class="relative">
                <canvas id="checklist_1" width="500" height="200"></canvas>
                <div class="absolute bottom-0 left-0 right-0 px-4 py-2 bg-white flex justify-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <span class="w-3 h-3 bg-blue-500 rounded-full"></span>
                        <span class="text-sm">José Venites</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="w-3 h-3 bg-purple-500 rounded-full"></span>
                        <span class="text-sm">Mario Rodriguez</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="w-3 h-3 bg-yellow-500 rounded-full"></span>
                        <span class="text-sm">Promedio general</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="p-6">
            <h2 class="text-2xl font-bold mb-4">Auditoría de control de calidad</h2>
            <div class="grid gap-4" style="grid-template-columns: 8fr 4fr">
                <div class="flex flex-col shadow-md p-4">
                    <p><span class="font-semibold">Tipo:</span> Recurrente</p>
                    <p class="mt-2 font-semibold">Periodo de aplicación</p>
                    <p class="ml-4"><span class="font-semibold">Inició en:</span> 27/05/2024 9:50</p>
                    <p class="ml-4"><span class="font-semibold">Finalizó en:</span> 27/05/2024 16:57</p>
                    <p class="ml-4"><span class="font-semibold">Duración:</span> 13:03:30</p>
                    <p class="mt-2"><span class="font-semibold">Auditores:</span> Christian Pérez</p>
                    <p class="mt-2"><span class="font-semibold">Unidad:</span> 6024 Universidad (CDMX)</p>
                    <p class="mt-2 font-semibold">Franquiciatarios:</p>
                    <p class="ml-4">1: José Luis Torrado</p>
                    <p class="ml-4">2: Eduardo Vargas</p>
                </div>
                <div class="bg-white p-4 flex items-center shadow-md">
                    <div class="" style="width: 100%;height: 100%;">
                        <span class="">Resultado general:</span>
                        <div class="text-4xl font-bold text-green-500 text-center mt-10">96<span
                                class="text-xl">/100</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="flex gap-4">
            <div class="bg-white shadow-md rounded-lg p-4 w-full md:w-2/3">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold">
                        Actividades respondidas
                    </h2>
                    <span class="text-sm">Última iteración</span>
                    <span class="text-xl font-bold">45/45</span>
                </div>
                <div class="grid grid-cols-5 gap-4 text-center">
                    <div>
                        <span class="block text-sm">Super</span>
                        <div class="w-8 h-8 my-2 bg-green-500 rounded-full mx-auto"></div>
                        <span class="block text-lg font-semibold">20</span>
                    </div>
                    <div>
                        <span class="block text-sm">Bueno</span>
                        <div class="w-8 h-8 my-2 bg-green-300 rounded-full mx-auto"></div>
                        <span class="block text-lg font-semibold">8</span>
                    </div>
                    <div>
                        <span class="block text-sm">Medio</span>
                        <div class="w-8 h-8 my-2 bg-yellow-500 rounded-full mx-auto"></div>
                        <span class="block text-lg font-semibold">7</span>
                    </div>
                    <div>
                        <span class="block text-sm">Bajo</span>
                        <div class="w-8 h-8 my-2 bg-yellow-200 rounded-full mx-auto"></div>
                        <span class="block text-lg font-semibold">4</span>
                    </div>
                    <div>
                        <span class="block text-sm">Deficiente</span>
                        <div class="w-8 h-8 my-2 bg-red-500 rounded-full mx-auto"></div>
                        <span class="block text-lg font-semibold">6</span>
                    </div>
                </div>
            </div>
            <div class="shadow-md rounded-lg p-4 w-full md:w-1/3">
                <h2 class="text-lg font-semibold mb-4">Complementos</h2>
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-2">
                            <img aria-hidden="true" alt="comments" src="https://placehold.co/16x16"
                                class="w-4 h-4" />
                            <span class="text-sm">Comentarios</span>
                        </div>
                        <span class="text-sm">6</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-2">
                            <img aria-hidden="true" alt="images" src="https://placehold.co/16x16"
                                class="w-4 h-4" />
                            <span class="text-sm">Imágenes</span>
                        </div>
                        <span class="text-sm">3</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-2">
                            <img aria-hidden="true" alt="signatures" src="https://placehold.co/16x16"
                                class="w-4 h-4" />
                            <span class="text-sm">Firmas</span>
                        </div>
                        <span class="text-sm">0</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="flex flex-col mt-6">
            <div class="bg-white p-4 shadow-md">
                <h3 class="text-xl font-bold mb-2">Resultado comparativo</h3>
                <p class=" mb-4">Periodo 03/2024 - 05/2024</p>
                <canvas id="canvas_checklist" width="w-full" height="h-auto"></canvas>
            </div>
            <div class="bg-white p-4 shadow-md">
                <div class="flex justify-start items-center mb-2">
                    <h3 class="text-xl font-bold">Comentarios</h3>
                    <span class="ml-4"> - Con inteligencia artificial</span>
                </div>
                <div class="text-sm">
                    <div class="flex items-center mb-2">
                        <span class="inline-block w-3 h-3 bg-green-500 rounded-full mr-2"></span>
                        <span>Super</span>
                    </div>
                    <ul class="list-disc list-inside  mb-4">
                        <li>Comienza con un puntaje alto, pero disminuye abruptamente en el tercer día.</li>
                        <li>Presenta fluctuaciones en los puntajes a lo largo del tiempo.</li>
                        <li>Puede haber inconsistencias que necesitan ser abordadas.</li>
                    </ul>
                    <div class="flex items-center mb-2">
                        <span class="inline-block w-3 h-3 bg-green-300 rounded-full mr-2"></span>
                        <span>Bueno</span>
                    </div>
                    <ul class="list-disc list-inside ">
                        <li>
                            Comienza con puntajes bajos, pero mejora gradualmente y se mantiene estable en niveles
                            más altos.
                        </li>
                        <li>
                            Puede haber habido problemas iniciales en el servicio al cliente que se han abordado y
                            mejorado con el tiempo.
                        </li>
                        <li>La consistencia en los puntajes es generalmente alta.</li>
                    </ul>
                    <div class="flex items-center mb-2">
                        <span class="inline-block w-3 h-3 bg-yellow-500 rounded-full mr-2"></span>
                        <span>Medio</span>
                    </div>
                    <ul class="list-disc list-inside ">
                        <li>
                            Comienza con puntajes bajos, pero mejora gradualmente y se mantiene estable en niveles
                            más altos.
                        </li>
                        <li>
                            Puede haber habido problemas iniciales en el servicio al cliente que se han abordado y
                            mejorado con el tiempo.
                        </li>
                        <li>La consistencia en los puntajes es generalmente alta.</li>
                    </ul>
                    <div class="flex items-center mb-2">
                        <span class="inline-block w-3 h-3 bg-yellow-200 rounded-full mr-2"></span>
                        <span>Bajo</span>
                    </div>
                    <ul class="list-disc list-inside ">
                        <li>
                            Comienza con puntajes bajos, pero mejora gradualmente y se mantiene estable en niveles
                            más altos.
                        </li>
                        <li>
                            Puede haber habido problemas iniciales en el servicio al cliente que se han abordado y
                            mejorado con el tiempo.
                        </li>
                        <li>La consistencia en los puntajes es generalmente alta.</li>
                    </ul>
                    <div class="flex items-center mb-2">
                        <span class="inline-block w-3 h-3 bg-red-500 rounded-full mr-2"></span>
                        <span>Deficiente</span>
                    </div>
                    <ul class="list-disc list-inside ">
                        <li>
                            Comienza con puntajes bajos, pero mejora gradualmente y se mantiene estable en niveles
                            más altos.
                        </li>
                        <li>
                            Puede haber habido problemas iniciales en el servicio al cliente que se han abordado y
                            mejorado con el tiempo.
                        </li>
                        <li>La consistencia en los puntajes es generalmente alta.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="flex flex-col mt-6">
            <div class="bg-white p-4 shadow-md">
                <h3 class="text-xl font-bold mb-2">Resultado detallado</h3>
                <p class=" mb-4">27/05/2024</p>
                <h3 class="text-xl font-bold mb-2">Zona de Preparación de Alimentos</h3>
            </div>
        </div>
        <div class="p-4 space-y-4">
            <h3 class="text-xl font-bold mb-2">Control de calidad</h3>
            <div class="shadow-md p-4">
                <div class="flex justify-between items-start">
                    <p>Identificar los riesgos específicos en el lugar de trabajo que requieren el uso de equipos de
                        protección
                        personal.</p>
                    <div class="flex items-center space-x-2">
                        <span>Super</span>
                        <span class="w-4 h-4 bg-green-500 rounded-full"></span>
                    </div>
                </div>
            </div>
            <div class="shadow-md p-4">
                <div class="flex justify-between items-start">
                    <p>Identificar los riesgos específicos en el lugar de trabajo que requieren el uso de equipos de
                        protección
                        personal.</p>
                    <div class="flex items-center space-x-2">
                        <span>Medio</span>
                        <span class="w-4 h-4 bg-yellow-500 rounded-full"></span>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="font-semibold">Adjuntos:</p>
                    <div class="grid gap-2 grid-cols-2 my-4 place-items-center">
                        <img src="https://placehold.co/250x250" alt="Adjunto 1" class="rounded-lg">
                        <img src="https://placehold.co/250x250" alt="Adjunto 2" class="rounded-lg">
                    </div>
                </div>
                <div class="mt-4">
                    <p class="font-semibold">Comentarios:</p>
                    <p class="mt-2 text-zinc-700">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis quis
                        enim mi.
                        Phasellus lobortis aliquam elementum. Nam quis mi risus. Nulla facilisi. Suspendisse quis porta
                        nunc, nec
                        scelerisque ex. Vestibulum tincidunt elementum bibendum. Phasellus quam quam, consectetur vitae
                        posuere sed,
                        dictum quis eros. Nullam sit amet urna ac erat hendrerit pharetra nec dignissim justo. Sed
                        pellentesque nunc
                        purus, interdum volutpat quam blandit quis.</p>
                </div>
            </div>
        </div>
    </div>
    <script>
        Chart.register(ChartDataLabels);
        const ctx = document.getElementById('myChart');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['21-09-23', '24-09-23', '27-09-23', '30-09-23', '31-09-23', '10-10-23'],
                datasets: [{
                        label: 'Auditoría de Control de Calidad',
                        data: [10, 2, 3, 5, 1, 7],
                        borderWidth: 1,
                        backgroundColor: 'rgba(255, 255, 0, 0.2)',
                        borderColor: 'rgba(255, 255, 0, 1)',
                        fill: 'origin',
                        fill: 'origin',
                        tension: 0.4,
                    },
                    {
                        label: 'Auditoría de Servicio al Cliente',
                        data: [5, 5, 12, 15, 18, 18],
                        borderWidth: 1,
                        backgroundColor: 'rgba(255, 0, 0, 0.2)', // Red with opacity
                        borderColor: 'rgba(255, 0, 0, 1)', // Solid red
                        fill: 'origin',
                        tension: 0.4,
                    },
                ]
            },
            max: 20,
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        min: 0,
                        max: 20,
                    }
                },
                animation: {
                    duration: 0
                },
            }
        });

        const checklist_1 = document.getElementById('checklist_1');
        new Chart(checklist_1, {
            type: 'bar',
            data: {
                labels: ['A1', 'A2', 'A3'],
                datasets: [{
                        label: 'Promedio',
                        data: [11, 10, 14],
                        borderWidth: 2,
                        backgroundColor: '#FFB700',
                        borderColor: '#FFB700',
                        type: 'line',
                        yAxisID: 'y',
                    },
                    {
                        label: 'Auditor: Jose Benitez',
                        data: [8, 12, 15],
                        borderWidth: 1,
                        backgroundColor: 'rgba(29, 137, 175, 0.2)',
                        borderColor: 'rgba(29, 137, 175, 1)',
                        yAxisID: 'y',
                    },
                    {
                        label: 'Auditor: Mario Rodriguez',
                        data: [15, 18, 16],
                        borderWidth: 1,
                        backgroundColor: 'rgba(206, 152, 254, 0.2)',
                        borderColor: 'rgba(206, 152, 254, 1)',
                        yAxisID: 'y',
                    },
                ],
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        min: 0,
                        max: 20,
                        ticks: {
                            callback: function(value) {
                                return value;
                            }
                        },
                        position: 'left',
                    },

                    y1: {
                        beginAtZero: true,
                        min: 0,
                        max: 20,
                        ticks: {
                            afterBuildTicks: function(axis) {
                                axis.ticks = [6, 15, 20];
                            },
                            callback: function(value) {
                                if (value === 20) {
                                    return 'Cumple';
                                } else if (value === 14) {
                                    return 'En Proceso';
                                } else if (value === 6) {
                                    return 'No cumple';
                                } else {
                                    return '';
                                }
                            },
                        },
                        position: 'right',
                        grid: {
                            drawOnChartArea: false, // Prevents grid lines from being drawn on the second axis
                        },
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    ChartDataLabels,
                },
                animation: {
                    duration: 0
                },
            }
        });
        const canvas_checklist = document.getElementById('canvas_checklist');
        new Chart(canvas_checklist, {
            type: 'bar',
            data: {
                labels: ['Super', 'Bueno', 'Medio', 'Bajo', 'Deficiente'],
                datasets: [{
                    label: 'My First Dataset',
                    data: [20, 8, 7, 4, 3],
                    backgroundColor: [
                        'rgba(34,197,94, 0.8)',
                        'rgba(134,239,172 , 0.8)',
                        'rgba(234,179,8 , 0.8)',
                        'rgba(254,240,138, 0.8)',
                        'rgba(239,68,68, 0.8)'
                    ],
                    borderColor: [
                        'rgba(34,197,94, 1)',
                        'rgba(134,239,172 , 1)',
                        'rgba(234,179,8 , 1)',
                        'rgba(254,240,138, 1)',
                        'rgba(239,68,68, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false
                    },
                    ChartDataLabels,
                },
                animation: {
                    duration: 0
                },
            }
        });
        const donut = document.getElementById('donut');
        const donut2 = document.getElementById('donut2');
        const donut3 = document.getElementById('donut3');

        const dataDonut = {
            labels: ['Rojo', 'Azul', 'Verde'],
            datasets: [{
                label: 'Mi Dataset',
                data: [30, 40, 30],
                backgroundColor: [
                    'rgba(52, 211, 153, 0.5)',
                    'rgba(251, 191, 36, 0.5)',
                    'rgba(249, 115, 22, 0.5)'
                ]
            }]
        };
        const optionDonut = {
            plugins: {
                legend: {
                    display: false
                }
            },
            cutout: '50',
            animation: {
                duration: 0
            },
        };
        new Chart(donut, {
            type: 'doughnut',
            data: dataDonut,
            options: optionDonut
        });
        new Chart(donut2, {
            type: 'doughnut',
            data: dataDonut,
            options: optionDonut
        });
        new Chart(donut3, {
            type: 'doughnut',
            data: dataDonut,
            options: optionDonut
        });
    </script>
</body>

</html>
