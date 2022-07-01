<template>
    <!--    <table class="custom-simple-table">-->
    <!--        <slot name="content"/>-->
    <!--    </table>-->
    <div class="table-scroll">
        <table class="main-table">
            <slot name="content"/>
        </table>
    </div>
</template>
<script>
export default {}
</script>
<style lang="scss">

.table-scroll {
    position: relative;
    width: 100%;
    z-index: 1;
    margin: auto;
    //overflow: auto;
    max-height: 16rem;
    border-bottom: 1px solid;
}

.table-scroll table {
    width: 100%;
    margin: auto;
    border-collapse: collapse;
    border-spacing: 0;
}

.table-wrap {
    position: relative;
}

.table-scroll th,
.table-scroll td {
    padding: 5px 10px;
    border: 1px solid #000;
    background: #fff;
    /*vertical-align: top;*/
}

.table-scroll thead th {
    background: #333;
    color: #fff;
    position: -webkit-sticky;
    position: sticky;
    top: 0;
}

.table-scroll {

    th:first-child {
        /*position: -webkit-sticky;*/
        /*position: sticky;*/
        left: 0;
        z-index: 2;
        //background: #ccc;
    }

}


thead th:first-child,
tfoot th:first-child {
    z-index: 5;
}

.table-scroll{
    tbody{
        td {
            border-top: 1px solid #E3E3E4;
            border-bottom: 1px solid #E3E3E4;
        }
    }
}

</style>

