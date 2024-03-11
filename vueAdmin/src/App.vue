<template>
  <div id="app">
    <router-view v-if="isRouterAlive"></router-view>
    <!-- <router-view></router-view> -->
  </div>
</template>

<script>
export default {
  name: 'app',
  provide(){
    return{
      reload:this.reload
    }
  },
  data(){
    return{
      isRouterAlive: true
    }
  },
  components: {},
  methods:{
    reload(){
      this.isRouterAlive = false
      this.$nextTick(function(){
        this.isRouterAlive = true
      })
    }
  }
}
</script>

<style lang="scss">
// 引入初始化样式
@import '@/style/main.scss';
@import '@/style/base.scss';
#app {
  background: #eee;
  height: 100vh;
  overflow: hidden;
}
</style>
