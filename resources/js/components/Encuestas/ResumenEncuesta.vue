<template>
    <div class="my-4">
        <h5 v-if="resume.count_users" v-text="`Total de encuestados: ${resume.count_users}`"></h5>
        <DefaultSimpleTable v-if="resume.questions_type_califica.length > 0">
            <template slot="content">
                <thead>
                <tr>
                    <th>Aspecto Evaluado</th>
                    <th>Promedio</th>
                    <th>T2B</th>
                    <th>MB</th>
                    <th>B</th>
                    <th>R</th>
                    <th>M</th>
                    <th>MM</th>
                </tr>
                </thead>
                <tbody>
                <tr
                    v-for="(question, index) in resume.questions_type_califica"
                    :key="index"
                >
                    <td class="w-40" v-text="question.titulo"></td>
                    <td class="w-10" v-text="question.prom"></td>
                    <td class="w-10" v-text="question.percent_califica_tb2+'%'"></td>
                    <td class="w-10" v-text="question.percent_califica_mb+'%'"></td>
                    <td class="w-10" v-text="question.percent_califica_b+'%'"></td>
                    <td class="w-10" v-text="question.percent_califica_r+'%'"></td>
                    <td class="w-10" v-text="question.percent_califica_m+'%'"></td>
                    <td class="w-10" v-text="question.percent_califica_mm+'%'"></td>
                </tr>
                </tbody>
            </template>
        </DefaultSimpleTable>
    </div>
</template>
<script>
export default {
    props:{
        resume:{
            required:true,
            default:{}
        }
    }
}
</script>