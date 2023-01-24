<template>
    <v-card-text :class="addClass">
        <div class="row head-table">
            <div class="col-sm-6" v-for="(header,i) in headers" :key="i" >
               {{header}}
            </div>
        </div>
        <slot name="content"/>
    </v-card-text>
</template>
<script>
    export default{
        props:{
            headers:{
                default:[]
            },
            addClass:{
                default:''
            }
        }
    }
</script>
<style>
.head-table{
  background: #EDF1F4;
  border-radius: 3px 3px 0px 0px;
  font-family: 'Tahoma';
  font-style: normal;
  font-weight: 400;
  line-height: 21px;
  /* identical to box height, or 150% */
  letter-spacing: 0.01em;
  color: #333D5D;
}
</style>
