import{p as s,a as n,o as t,c as o,b as a,d as e,t as i,w as l,e as c,f as d}from"./vendor.d5fc183b.js";const g=new URLSearchParams(window.location.search).get("target"),r={data:()=>({isUnfold:!1,showUnfold:!1,targetLink:decodeURIComponent(g),maxHeight:"44px"}),methods:{unfold(){this.maxHeight="none",this.showUnfold=!1},gotoLink(){window.location.href=this.targetLink.replace(/^javascript:/i,"")}},mounted(){this.$refs.link.getBoundingClientRect().height>50&&(this.showUnfold=!0)}},h=l();s("data-v-c728d904");const f={class:"middle-page"},m=a("img",{class:"logo",src:"//lf-cdn-tos.bytescm.com/obj/static/link_juejin_cn/assets/logo_new.0ec938fb.svg"},null,-1),p={class:"content"},k=a("p",{class:"title"},"即将离开建行家装分期，请注意账号财产安全",-1),w={class:"link-container"},u={key:0,class:"ellipsis"},v=c(" ...");n();const b=h(((s,n,l,c,d,g)=>(t(),o("div",f,[m,a("div",p,[k,a("div",w,[a("div",{class:"link-content",style:{maxHeight:s.maxHeight}},[s.showUnfold?(t(),o("span",u,[v,a("span",{class:"unfold",onClick:n[1]||(n[1]=(...s)=>g.unfold&&g.unfold(...s))},"展开")])):e("",!0),a("p",{style:{margin:"0px"},ref:"link"},i(s.targetLink),513)],4)]),a("button",{class:"btn",onClick:n[2]||(n[2]=(...s)=>g.gotoLink&&g.gotoLink(...s))},"继续访问")])]))));r.render=b,r.__scopeId="data-v-c728d904",d(r).mount("#app");
