(self.webpackChunk_N_E=self.webpackChunk_N_E||[]).push([[7879],{90843:function(e,r,n){Promise.resolve().then(n.bind(n,61952)),Promise.resolve().then(n.bind(n,80354)),Promise.resolve().then(n.bind(n,54062)),Promise.resolve().then(n.bind(n,88198)),Promise.resolve().then(n.bind(n,28411)),Promise.resolve().then(n.bind(n,23391)),Promise.resolve().then(n.bind(n,95457)),Promise.resolve().then(n.bind(n,67472)),Promise.resolve().then(n.bind(n,96062)),Promise.resolve().then(n.bind(n,34399)),Promise.resolve().then(n.bind(n,67501)),Promise.resolve().then(n.bind(n,54742)),Promise.resolve().then(n.bind(n,96977)),Promise.resolve().then(n.bind(n,90264)),Promise.resolve().then(n.bind(n,38718)),Promise.resolve().then(n.bind(n,28770)),Promise.resolve().then(n.bind(n,23097)),Promise.resolve().then(n.bind(n,59746)),Promise.resolve().then(n.bind(n,26530)),Promise.resolve().then(n.bind(n,58138)),Promise.resolve().then(n.bind(n,16910)),Promise.resolve().then(n.bind(n,84841)),Promise.resolve().then(n.bind(n,85256)),Promise.resolve().then(n.bind(n,32891)),Promise.resolve().then(n.bind(n,60658)),Promise.resolve().then(n.bind(n,37290)),Promise.resolve().then(n.bind(n,48167)),Promise.resolve().then(n.bind(n,60200)),Promise.resolve().then(n.bind(n,32870)),Promise.resolve().then(n.bind(n,57578)),Promise.resolve().then(n.bind(n,54876)),Promise.resolve().then(n.bind(n,31397)),Promise.resolve().then(n.bind(n,6314)),Promise.resolve().then(n.bind(n,69151)),Promise.resolve().then(n.bind(n,69720)),Promise.resolve().then(n.bind(n,14371)),Promise.resolve().then(n.bind(n,51835)),Promise.resolve().then(n.bind(n,94439)),Promise.resolve().then(n.bind(n,84204)),Promise.resolve().then(n.bind(n,62414)),Promise.resolve().then(n.bind(n,65935)),Promise.resolve().then(n.bind(n,91246)),Promise.resolve().then(n.bind(n,18713)),Promise.resolve().then(n.bind(n,81368)),Promise.resolve().then(n.bind(n,10736)),Promise.resolve().then(n.bind(n,45700)),Promise.resolve().then(n.bind(n,43983)),Promise.resolve().then(n.bind(n,26387)),Promise.resolve().then(n.bind(n,15151)),Promise.resolve().then(n.bind(n,94661)),Promise.resolve().then(n.bind(n,4575)),Promise.resolve().then(n.bind(n,32738)),Promise.resolve().then(n.bind(n,84486)),Promise.resolve().then(n.bind(n,46636)),Promise.resolve().then(n.bind(n,55790)),Promise.resolve().then(n.bind(n,52536)),Promise.resolve().then(n.bind(n,20013)),Promise.resolve().then(n.t.bind(n,231,23)),Promise.resolve().then(n.bind(n,10921)),Promise.resolve().then(n.bind(n,49917)),Promise.resolve().then(n.bind(n,97293)),Promise.resolve().then(n.bind(n,54646)),Promise.resolve().then(n.bind(n,74004)),Promise.resolve().then(n.bind(n,25628)),Promise.resolve().then(n.bind(n,81097))},49917:function(e,r,n){"use strict";n.d(r,{default:function(){return w}});var o=n(57437),i=n(2265),t=n(99240),s=n(64973),l=n(40191),a=n(87138),c=n(27297),d=n(44782),h=n(23789),m="/_next/static/media/tamo-junto-banner.dd60868d.png",u=n(6350);function g(e){let{isAdmin:r}=e;return(0,o.jsx)(t.k,{w:"100%",bg:u.O.COLOR_TJ_CONTA_OPACITY,color:"white",align:{base:"center",lg:"center"},justify:{base:"center",lg:"space-between"},pt:{base:"2rem",lg:"0"},children:(0,o.jsxs)(h.M,{gap:{base:"1rem",lg:"2rem"},direction:{base:"column-reverse",lg:"row"},alignItems:"center",justifyContent:"space-between",children:[(0,o.jsx)(s.E,{display:{base:"none",lg:"block"},src:m,alt:"Pessoas unindo as m\xe3os",width:"auto",height:"320px"}),(0,o.jsx)(s.E,{display:{base:"block",lg:"none"},src:m,alt:"Pessoas unindo as m\xe3os",width:"100%",mb:{base:"1rem",lg:"0"}}),(0,o.jsxs)(t.k,{direction:"column",maxW:{base:"90%",lg:"500px"},textAlign:{base:"center",lg:"left"},children:[(0,o.jsx)(c.X,{color:u.O.COLOR_TJ_CONTA,fontSize:{base:"2xl",lg:"3xl"},lineHeight:"short",mb:"0.5rem",children:"Crie sua vaquinha em 2 minutos"}),(0,o.jsx)(l.x,{fontSize:{base:"md",lg:"xl"},lineHeight:"normal",fontWeight:"400",mb:"1.5rem",color:"#383F47",children:"Cadastre sua vaquinha de doa\xe7\xe3o de forma simples e eficiente contando com toda seguran\xe7a e suporte da Tamo Junto."}),(0,o.jsx)(d.r,{as:a.default,w:{base:"100%",lg:"13.5rem"},border:"none",variant:"secondary",href:r?"/create-campaign":"/",isDisabled:!r,bg:u.O.COLOR_CAMPAIGNS_COMMON,color:"white",children:"Criar minha vaquinha"})]})]})})}var b=n(77890),x=n(67249),f=n(68587),p=n(752),v=n(31014),O=n(16463),C=n(39343),j=n(59772);let A=j.z.object({theme:j.z.string().optional(),location:j.z.string().default("ALL"),category:j.z.string().default("ALL")});function P(e){var r,n,s,a;let{campaignsSlugs:c}=e,[h,m]=(0,i.useState)(!1),{ufs:u,categories:g,fetchAmountRaised:j,fetchUfs:P}=(0,p.t)(),{register:w,handleSubmit:_,setValue:S,watch:y,formState:{errors:R}}=(0,C.cI)({resolver:(0,v.F)(A)}),T=(0,O.useRouter)(),I=(0,O.useSearchParams)();0===(null!==(a=y("theme"))&&void 0!==a?a:"").length&&h&&(T.push("/",{scroll:!1}),m(!1));let L=I.get("category"),N=g.find(e=>e.name===L);return(0,i.useEffect)(()=>{j({page:1,slugs:c})},[]),(0,i.useEffect)(()=>{P()},[]),(0,i.useEffect)(()=>{let e=I.get("search"),r=I.get("state"),n=I.get("categoryId");e&&S("theme",e),r&&S("location",r),n&&S("category",n)},[I,S]),(0,o.jsxs)(t.k,{borderRadius:"10px",boxShadow:"0px 4px 4px 0px rgba(56, 63, 71, 0.10)",w:{base:"95vw",lg:"90%"},mb:{base:"2rem",lg:"unset"},p:"1.5rem",bg:"white",alignItems:"center",flexDirection:"column",gap:"1rem",children:[(0,o.jsx)(l.x,{fontSize:"1.5rem",color:"black.200",fontWeight:"700",lineHeight:"normal",children:"Procurar vaquinha"}),(0,o.jsxs)(t.k,{as:"form",onSubmit:_(e=>{let r=new URLSearchParams(I.toString());e.theme&&r.set("search",e.theme),"ALL"!==e.location?r.set("state",e.location):r.delete("state"),"ALL"!==e.category?r.set("categoryId",e.category):r.delete("categoryId"),T.replace("?".concat(r.toString()),{scroll:!1}),m(!0)}),w:"100%",gap:"1rem",flexWrap:{base:"wrap",lg:"unset"},children:[(0,o.jsx)(b.I,{_placeholder:{color:"#B3B3B3"},placeholder:"O que voc\xea est\xe1 procurando?",w:{base:"100%",lg:"312px"},error:null===(r=R.theme)||void 0===r?void 0:r.message,...w("theme")}),(0,o.jsx)(f.i,{placeholderOption:"Todas as localiza\xe7\xf5es",listArray:u,w:{base:"100%",lg:"253px"},color:"black.200",error:null===(n=R.location)||void 0===n?void 0:n.message,...w("location")}),(0,o.jsx)(x.Y,{w:{base:"100%",lg:"253px"},placeholderOption:"Todas as categorias",color:"black.200",categorySelected:N,listArray:g,error:null===(s=R.category)||void 0===s?void 0:s.message,...w("category")}),(0,o.jsx)(d.r,{p:"1.5rem",type:"submit",variant:"primary",w:{base:"100%",lg:"30%"},children:"Buscar"})]})]})}var w=function(e){let{campaignsSlugs:r}=e;return(0,o.jsxs)(o.Fragment,{children:[(0,o.jsx)(g,{isAdmin:!0}),!1,(0,o.jsx)(h.M,{justifyContent:"center",mt:{base:"1rem",lg:"3rem"},mb:{base:"1rem",lg:"3rem"},children:(0,o.jsx)(P,{campaignsSlugs:r})})]})}},97293:function(e,r,n){"use strict";n.d(r,{DetailsCampaignCard:function(){return h}});var o=n(57437),i=n(752),t=n(6350),s=n(74680),l=n(76072),a=n(99240),c=n(40191),d=n(22316);function h(e){var r;let{campaignSlug:n,goalAmount:h,amountCollected:m,isCounted:u}=e,{amountRaised:g}=(0,i.t)(),b=null===(r=g.find(e=>e.slug===n))||void 0===r?void 0:r.amountCollected,x=(0,s.Y)({amountCollected:m,goalAmount:h});return(0,o.jsxs)(o.Fragment,{children:[(0,o.jsxs)(a.k,{w:"100%",alignItems:"center",gap:"8px",children:[(0,o.jsxs)(c.x,{color:"black.200",fontWeight:"700",fontSize:"1rem",lineHeight:"normal",children:[x,"%"]}),(0,o.jsx)(d.E,{value:x,w:"100%",size:"sm",borderRadius:"8px",sx:u?{"& > div":{backgroundColor:t.O.COLOR_TJ_CONTA}}:{"& > div":{backgroundColor:t.O.COLOR_CAMPAIGNS_COMMON}}})]}),(0,o.jsxs)(a.k,{flexDirection:"column",children:[(0,o.jsxs)(c.x,{fontSize:"1rem",fontWeight:"400",lineHeight:"normal",color:"#C7C7C7",children:[(0,o.jsx)(c.x,{as:"span",fontSize:"0.75rem",children:"Meta:"})," ",(0,l.$)(h)]}),(0,o.jsxs)(c.x,{fontSize:"1.5rem",fontWeight:"700",lineHeight:"normal",color:"black.200",children:[(0,o.jsx)(c.x,{as:"span",fontSize:"1rem",fontWeight:"400",children:"Arrecadado:"})," ",b?(0,l.$)(b):(0,l.$)(0)]})]})]})}},27297:function(e,r,n){"use strict";n.d(r,{X:function(){return t}});var o=n(57437),i=n(40191);function t(e){let{children:r,...n}=e;return(0,o.jsx)(i.x,{as:"h2",fontSize:{base:"1.5rem",lg:"3rem"},color:"black.200",lineHeight:"normal",fontWeight:"700",...n,children:r})}},74004:function(e,r,n){"use strict";n.d(r,{default:function(){return A}});var o=n(57437),i=n(99240),t=n(65562),s=n(64973),l=n(76605),a=n(14937),c=n(87138),d=n(27297),h=n(23789),m=n(25668),u=n(44782),g=n(74340),b=n(33682),x=n(6353),f=n(40191),p=n(23051),v=n(6350),O=n(16356);function C(e){let{isFees:r=!1}=e,n=[{id:1,value:"R$ 0,00",label:"Cadastro",highlight:"GR\xc1TIS",color:v.O.COLOR_CAMPAIGNS_COMMON,iconColor:v.O.COLOR_CAMPAIGNS_COMMON,bg:v.O.COLOR_CAMPAIGNS_COMMON_OPACITY,cardBg:"white",icon:p.Vqw},{id:2,value:"R$ 0,00",label:"Criar Vaquinha",highlight:"GR\xc1TIS",color:v.O.COLOR_TJ_CONTA,iconColor:v.O.COLOR_TJ_CONTA,bg:v.O.COLOR_TJ_CONTA_OPACITY,cardBg:"white",icon:p.v0Q},{id:3,value:"R$ 0,00",label:"Saque",highlight:"GR\xc1TIS",color:v.O.COLOR_CAMPAIGNS_COMMON,iconColor:v.O.COLOR_CAMPAIGNS_COMMON,bg:v.O.COLOR_CAMPAIGNS_COMMON_OPACITY,cardBg:"white",icon:p.WoA},{id:4,value:"R$ 0,00",label:"Suporte",highlight:"GR\xc1TIS",color:v.O.COLOR_TJ_CONTA,iconColor:r?v.O.COLOR_TJ_CONTA_OPACITY:v.O.COLOR_TJ_CONTA,bg:r?v.O.COLOR_TJ_CONTA:v.O.COLOR_TJ_CONTA_OPACITY,cardBg:r?v.O.COLOR_TJ_CONTA_OPACITY:"white",icon:O.E0L}];return(0,o.jsxs)(g.xu,{bg:"white",p:{base:4,md:8},borderRadius:"md",boxShadow:"sm",maxW:{base:"90%",md:"60%"},children:[!r&&(0,o.jsx)(b.X,{as:"h2",mb:4,fontSize:{base:"1.5rem",md:"2rem"},fontWeight:500,color:"#383F47",textAlign:"center",children:"Porque nos escolhem tanto"}),(0,o.jsx)(i.k,{justify:"center",align:"stretch",wrap:"wrap",gap:{base:4,md:6},children:n.map(e=>(0,o.jsxs)(i.k,{direction:"column",flexBasis:32,align:{base:"center",md:"stretch"},minW:{base:"90px",md:"120px"},color:e.color,borderRadius:"md",bg:e.cardBg,p:4,boxShadow:"base",children:[e.icon&&(0,o.jsx)(g.xu,{display:"flex",alignItems:"center",justifyContent:"center",placeSelf:"center",borderRadius:"full",bg:e.bg,p:3,mb:2,children:(0,o.jsx)(x.J,{as:e.icon,boxSize:7,color:e.iconColor})}),(0,o.jsx)(f.x,{fontSize:"xl",fontWeight:"bold",mb:1,children:e.value}),(0,o.jsxs)(f.x,{fontSize:"xs",color:"gray.600",children:[e.label," "]}),(0,o.jsx)(f.x,{as:"span",fontSize:"xs",color:"gray.500",children:e.highlight})]},e.id))})]})}var j=n(29517),A=function(){let{verifyIsAdmin:e}=(0,j.m)();return(0,o.jsxs)(i.k,{as:"section",flexDirection:{base:"column",lg:"row"},position:"relative",justifyContent:"center",bg:v.O.BACKGROUND_COLOR,py:"1rem",children:[(0,o.jsx)(t.d,{above:"lg",children:(0,o.jsxs)(i.k,{position:"relative",w:"100%",justifyContent:"flex-end",alignItems:"center",children:[(0,o.jsx)(s.E,{src:"/_next/static/media/tamo-junto-home.0ac7ae30.png",alt:"Tamo Junto Desktop Banner",objectFit:"contain"}),(0,o.jsxs)(h.M,{position:"absolute",top:"50%",left:"50%",transform:"translate(-50%, -50%)",flexDirection:"column",gap:"1rem",children:[(0,o.jsxs)(d.X,{color:"white",children:["Fique tranquilo, voc\xea ",(0,o.jsx)("br",{})," n\xe3o est\xe1 sozinho. ",(0,o.jsx)("br",{})," Tamo Junto!"]}),(0,o.jsxs)(m.T,{color:"white",children:["Crie uma vaquinha para voc\xea, para um amigo ou para seu",(0,o.jsx)("br",{})," cachorro caramelo. Conte com a m\xe1xima seguran\xe7a e todo",(0,o.jsx)("br",{})," suporte do nosso time."]}),(0,o.jsx)(u.r,{as:c.default,w:"20rem",border:"none",variant:"primary",href:"/create-campaign",children:"Crie sua vaquinha para arrecadar"}),(0,o.jsx)(C,{})]})]})}),(0,o.jsx)(l.c,{above:"lg",children:(0,o.jsxs)(a.g,{w:"100%",h:"auto",position:"relative",children:[(0,o.jsxs)(h.M,{flexDirection:"column",gap:"1rem",children:[(0,o.jsxs)(d.X,{fontSize:"1.75rem",color:"white",children:["Fique tranquilo, voc\xea n\xe3o ",(0,o.jsx)("br",{})," est\xe1 sozinho.",(0,o.jsx)("br",{})," Tamo Junto!"]}),(0,o.jsx)(m.T,{color:"white",children:"Crie uma vaquinha para voc\xea, para um amigo ou para seu cachorro caramelo. Conte com a m\xe1xima seguran\xe7a e todo suporte do nosso time."}),(0,o.jsx)(u.r,{as:c.default,w:"20rem",border:"none",variant:"primary",href:"/create-campaign",children:"Crie sua vaquinha para arrecadar"})]}),(0,o.jsx)(s.E,{src:"/_next/static/media/tamo-junto-home-mob.d337aab6.png",alt:"Tamo Junto Mobile Banner",w:"100vw",objectFit:"cover"}),(0,o.jsx)(g.xu,{display:"flex",alignItems:"center",justifyContent:"center",position:"absolute",bottom:"1rem",w:"100%",p:"1rem",children:(0,o.jsx)(C,{})})]})})]})}},77890:function(e,r,n){"use strict";n.d(r,{I:function(){return f}});var o=n(57437),i=n(19769),t=n(20864),s=n(40191),l=n(34437),a=n(44379),c=n(90371),d=n(70671),h=n(2265),m=n(58918),u=n.n(m),g=n(6350);let b=(0,h.forwardRef)((e,r)=>{let n=(0,h.useRef)(null);(0,h.useImperativeHandle)(r,()=>({get value(){return n.current?n.current.value:""},set value(val){n.current&&(n.current.value=null!=val?val:"")},focus(){n.current&&n.current.focus()}}));let{width:m,error:b,isDisabled:x,isReadOnly:f,readOnly:p,label:v,name:O,mask:C,rightIcon:j,optional:A=!1,...P}=e;return(0,o.jsxs)(i.NI,{position:"relative",width:m,isInvalid:!!b,isDisabled:x,isReadOnly:f||p,children:[!!v&&(0,o.jsx)(t.l,{htmlFor:O,fontSize:"0.875rem",fontWeight:"400",lineHeight:"normal",children:v}),A&&(0,o.jsx)(s.x,{fontSize:"0.875rem",fontWeight:"400",lineHeight:"normal"}),(0,o.jsxs)(l.B,{children:[(0,o.jsx)(a.I,{ref:r,inputRef:e=>n.current=e,as:u(),mask:C,name:O,size:"lg",fontWeight:"500",fontSize:"1rem",lineHeight:"1.5rem",borderWidth:"1px",borderColor:"gray.200",borderRadius:"60px",transition:"all 0.2s",_focus:b?{borderColor:"transparent",boxShadow:"none"}:{outline:"none",boxShadow:"0 0 0 1.5px ".concat(g.O.OUTLINE_SELECT_COLOR),borderColor:"transparent"},_disabled:{color:"gray.400"},_invalid:{borderColor:"yellow.500"},_placeholder:{color:"#B3B3B3"},_readOnly:{borderColor:"transparent",userSelect:"none",pl:"0rem"},...x?{_hover:{}}:{_hover:{borderColor:f||p?"transparent":"gray.300"}},...P}),j&&(0,o.jsx)(c.x,{mr:2,mt:1,children:j})]}),(0,o.jsx)(d.J1,{color:"yellow.500",children:b})]})});b.displayName="InputMask";let x=(0,h.forwardRef)((e,r)=>{var n;if((null==e?void 0:e.mask)&&(null==e?void 0:null===(n=e.mask)||void 0===n?void 0:n.length)>0)return(0,o.jsx)(b,{...e,ref:r});let{name:h,label:m,mask:u="",error:x=null,optional:f=!1,width:p,readOnly:v=!1,isDisabled:O,isReadOnly:C,rightIcon:j,...A}=e;return(0,o.jsxs)(i.NI,{position:"relative",width:p,isInvalid:!!x,isDisabled:O,isReadOnly:C||v,children:[!!m&&(0,o.jsx)(t.l,{htmlFor:h,fontSize:"0.875rem",fontWeight:"400",lineHeight:"normal",color:"#263238",children:m}),f&&(0,o.jsx)(s.x,{fontSize:"0.875rem",fontWeight:"400",lineHeight:"normal"}),(0,o.jsxs)(l.B,{children:[(0,o.jsx)(a.I,{ref:r,name:h,size:"lg",fontWeight:"500",fontSize:"1rem",rightIcon:j,lineHeight:"1.5rem",color:"gray.700",borderWidth:"1px",borderColor:"gray.200",borderRadius:"60px",transition:"all 0.2s",_focus:x?{borderColor:"transparent",boxShadow:"none"}:{outline:"none",boxShadow:"0 0 0 1.5px ".concat(g.O.OUTLINE_SELECT_COLOR),borderColor:"transparent"},_disabled:{color:"gray.400"},_invalid:{borderColor:"yellow.500"},_placeholder:{color:"#B3B3B3"},_readOnly:{borderColor:"transparent",userSelect:"none",pl:"0rem"},...O?{_hover:{}}:{_hover:{borderColor:C||v?"transparent":"gray.300"}},...A}),j&&(0,o.jsx)(c.x,{mr:2,mt:1,children:j})]}),(0,o.jsx)(d.J1,{color:"yellow.500",children:x})]})});x.displayName="InputBase";let f=x},82096:function(e,r,n){"use strict";function o(){return null}n.d(r,{Z:function(){return o}}),n(81984)},25628:function(e,r,n){"use strict";n.d(r,{ListCategories:function(){return h}});var o=n(57437),i=n(99240),t=n(35500),s=n(64973),l=n(95199),a=n(25668),c=n(87138),d=n(82096);function h(e){let{categories:r}=e,[n]=(0,l.E)({breakpoints:{"(min-width: 600px)":{slides:{perView:3,spacing:10}},"(min-width: 800px)":{slides:{perView:4,spacing:10}},"(min-width: 1000px)":{slides:{perView:6,spacing:0}}},slides:{perView:1.9,spacing:10}});return 0===r.length?(0,o.jsx)(a.T,{textAlign:"center",fontSize:"1rem",fontWeight:"700",children:"Carregando..."}):(0,o.jsxs)(o.Fragment,{children:[(0,o.jsx)(d.Z,{}),(0,o.jsx)(i.k,{ref:n,className:"keen-slider",justifyContent:"space-between",children:r.map(e=>(0,o.jsx)(m,{imgUrl:e.imageUrl,name:e.name,id:e.id,alt:e.name},e.id.toString()))})]})}function m(e){let{imgUrl:r,name:n,alt:i,id:l}=e;return(0,o.jsxs)(t.r,{as:c.default,display:"flex",flexDirection:"column",w:"169px",alignItems:"center",href:"?categoryId=".concat(l,"#destaques"),className:"keen-slider__slide",children:[(0,o.jsx)(s.E,{objectFit:"cover",width:169,height:169,src:r,borderRadius:169,alt:i}),(0,o.jsx)(a.T,{fontSize:"1rem",fontWeight:"700",mt:"12px",children:n})]})}},67249:function(e,r,n){"use strict";n.d(r,{Y:function(){return d}});var o=n(57437),i=n(6350),t=n(19769),s=n(20864),l=n(43075),a=n(70671);let c=(0,n(2265).forwardRef)((e,r)=>{let{error:n,listArray:c,placeholderOption:d,label:h,categorySelected:m}=e;return(0,o.jsx)(o.Fragment,{children:(0,o.jsxs)(t.NI,{w:"100%",isInvalid:!!n,children:[!!h&&(0,o.jsx)(s.l,{fontSize:"0.875rem",fontWeight:"400",lineHeight:"normal",children:h}),(0,o.jsxs)(l.P,{ref:r,w:"15rem",h:"3rem",color:"gray.700",fontSize:"1rem",lineHeight:"normal",fontWeight:"500",borderRadius:"60px",_focus:{outline:"none",boxShadow:"0 0 0 1.5px ".concat(i.O.OUTLINE_SELECT_COLOR),borderColor:"transparent"},_disabled:{color:"gray.400"},_invalid:{borderColor:"yellow.500"},...e,children:[(0,o.jsx)("option",{value:"ALL",children:d}),!!c&&c.length>0&&c.map(e=>(0,o.jsx)("option",{selected:!!m&&m.id.toString()===e.id.toString(),value:e.id.toString(),children:e.name},e.id.toString()))]}),(0,o.jsx)(a.J1,{color:"yellow.500",children:n})]})})});c.displayName="SelectBase";let d=c},68587:function(e,r,n){"use strict";n.d(r,{i:function(){return d}});var o=n(57437),i=n(6350),t=n(19769),s=n(20864),l=n(43075),a=n(70671);let c=(0,n(2265).forwardRef)((e,r)=>{let{error:n,listArray:c,placeholderOption:d,label:h}=e;return(0,o.jsx)(o.Fragment,{children:(0,o.jsxs)(t.NI,{w:"100%",isInvalid:!!n,children:[!!h&&(0,o.jsx)(s.l,{fontSize:"0.875rem",fontWeight:"400",lineHeight:"normal",children:h}),(0,o.jsxs)(l.P,{ref:r,w:"15rem",h:"3rem",color:"gray.700",fontSize:"1rem",lineHeight:"normal",fontWeight:"500",borderRadius:"60px",_focus:{outline:"none",boxShadow:"0 0 0 1.5px ".concat(i.O.OUTLINE_SELECT_COLOR),borderColor:"transparent"},_disabled:{color:"gray.400"},_invalid:{borderColor:"yellow.500"},...e,children:[(0,o.jsx)("option",{value:"ALL",children:d}),!!c&&c.length>0&&c.map(e=>(0,o.jsx)("option",{value:e.uf,children:e.uf},e.uf))]}),(0,o.jsx)(a.J1,{color:"yellow.500",children:n})]})})});c.displayName="SelectBase";let d=c},81097:function(e,r,n){"use strict";n.d(r,{SliderComponent:function(){return l}});var o=n(57437),i=n(99240),t=n(95199),s=n(82096);function l(e){let{children:r,...n}=e,[l]=(0,t.E)({breakpoints:{"(min-width: 558px)":{slides:{perView:2.25,spacing:20}},"(min-width: 1000px)":{slides:{perView:3,spacing:20}}},slides:{perView:1.5,spacing:20}});return(0,o.jsxs)(o.Fragment,{children:[(0,o.jsx)(s.Z,{}),(0,o.jsx)(i.k,{ref:l,className:"keen-slider",py:"5px",...n,children:r})]})}},74680:function(e,r,n){"use strict";function o(e){let{amountCollected:r,goalAmount:n}=e;return Math.floor(r/n*100)}n.d(r,{Y:function(){return o}})},76072:function(e,r,n){"use strict";function o(e){return new Intl.NumberFormat("pt-BR",{style:"currency",currency:"BRL"}).format(e)}n.d(r,{$:function(){return o}})},10921:function(e,r,n){"use strict";n.r(r),r.default={src:"/_next/static/media/image-default.482751ca.png",height:320,width:459,blurDataURL:"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAgAAAAGCAMAAADJ2y/JAAAAGFBMVEXN9Iy56WrR95PY+qCt41az52DC7X3I84Jo4scZAAAAAnRSTlP8++T1SB0AAAAJcEhZcwAACxMAAAsTAQCanBgAAAApSURBVHicJcpBDgAgEMLAAqL//7FZzdya0qwBPgPwpFooTSOz/eH3WhcQ8gCdOKrx8gAAAABJRU5ErkJggg==",blurWidth:8,blurHeight:6}}},function(e){e.O(0,[2406,522,6051,9383,8439,225,7653,6853,314,1374,5163,1454,1397,3527,1350,3944,4646,752,2971,7023,1744],function(){return e(e.s=90843)}),_N_E=e.O()}]);