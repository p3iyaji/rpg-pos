import{r as g,f as r,d as a,e,m as x,t,i as u,h as c,F as k,q as I}from"./index-9pQyjWak.js";const P={class:"fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"},T={class:"bg-white rounded-lg p-6 w-full max-w-md"},_={class:"flex justify-between items-center mb-4"},C={class:"max-h-[70vh] overflow-y-auto"},O={class:"invoice-header"},D={class:"customer-info"},j={key:0},S={class:"items-list"},q={class:"item-name"},$={class:"item-qty"},F={class:"item-price"},L={class:"item-total"},N={class:"summary"},A={class:"summary-row"},E={key:0,class:"summary-row"},M={key:1,class:"summary-row"},R={class:"summary-row total"},z={class:"payment-info"},B={key:0},V={class:"footer"},Y={__name:"ThermalInvoice",props:{order:{type:Object,required:!0},businessInfo:{type:Object,default:()=>({name:"RPG-POS",address:"123 Main St, City",phone:"0800-123-4567",footer:"Returns within 7 days with receipt"})}},setup(n,{expose:h}){const d=n,m=g(null),f=()=>new Date(d.order.date).toLocaleString(),y=d.businessInfo.name||"RPG-POS",v=d.businessInfo.address||"",w=d.businessInfo.phone||"",b=d.businessInfo.footer||"Thank you for your business!",o=l=>`₦${parseFloat(l).toFixed(2)}`,p=()=>{if(!m.value){console.error("Invoice content not found");return}const l=window.open("","_blank");l.document.write(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>Invoice #${d.order.order_no}</title>
      <style>
        body {
          font-family: 'Courier New', monospace;
          font-size: 12px;
          width: 80mm;
          margin: 0;
          padding: 5px;
        }
        .thermal-invoice {
          width: 100%;
        }
        .invoice-header, .footer {
          text-align: center;
          margin-bottom: 10px;
        }
        .invoice-header h2 {
          font-size: 14px;
          margin: 5px 0;
        }
        .items-header, .item-row {
          display: flex;
          justify-content: space-between;
          margin-bottom: 3px;
        }
        .item-name {
          width: 40%;
          overflow: hidden;
          text-overflow: ellipsis;
        }
        .item-qty {
          width: 15%;
          text-align: right;
        }
        .item-price {
          width: 20%;
          text-align: right;
        }
        .item-total {
          width: 25%;
          text-align: right;
        }
        .summary {
          margin-top: 10px;
          border-top: 1px dashed #000;
          padding-top: 5px;
        }
        .summary-row {
          display: flex;
          justify-content: space-between;
          margin-bottom: 3px;
        }
        .total {
          font-weight: bold;
          border-top: 1px dashed #000;
          padding-top: 5px;
        }
        .customer-info, .payment-info {
          margin: 5px 0;
        }
      </style>
    </head>
    <body>
      ${m.value.innerHTML}
      <script>
        window.onload = function() {
          setTimeout(function() {
            window.print();
            window.close();
          }, 100);
        };
      <\/script>
    </body>
    </html>
  `),l.document.close()};return h({printInvoice:p}),(l,s)=>(a(),r("div",P,[e("div",T,[e("div",_,[s[1]||(s[1]=e("h3",{class:"text-lg font-bold"},"Invoice Preview",-1)),e("button",{onClick:s[0]||(s[0]=i=>l.$emit("close")),class:"text-gray-500 hover:text-gray-700"}," ✕ ")]),e("div",C,[e("div",{ref_key:"invoiceContent",ref:m,class:"thermal-invoice p-4"},[e("div",O,[e("h2",null,t(u(y)),1),e("p",null,t(u(v)),1),e("p",null,t(u(w)),1),e("p",null,"Order #: "+t(n.order.order_no),1),e("p",null,"Date: "+t(f()),1)]),e("div",D,[e("p",null,"Customer: "+t(n.order.customer.name),1),n.order.customer.phone?(a(),r("p",j,"Phone: "+t(n.order.customer.phone),1)):c("",!0)]),s[7]||(s[7]=x('<div class="items-header"><span class="item-name">ITEM</span><span class="item-qty">QTY</span><span class="item-price">PRICE</span><span class="item-total">TOTAL</span></div>',1)),e("div",S,[(a(!0),r(k,null,I(n.order.items,i=>(a(),r("div",{key:i.id,class:"item-row"},[e("span",q,t(i.name||`Product ${i.product_id}`),1),e("span",$,t(i.quantity),1),e("span",F,t(o(i.unit_price)),1),e("span",L,t(o(i.total)),1)]))),128))]),e("div",N,[e("div",A,[s[2]||(s[2]=e("span",null,"Subtotal:",-1)),e("span",null,t(o(n.order.subtotal)),1)]),n.order.product_discounts>0?(a(),r("div",E,[s[3]||(s[3]=e("span",null,"Product Discounts:",-1)),e("span",null,"-"+t(o(n.order.product_discounts)),1)])):c("",!0),n.order.general_discount>0?(a(),r("div",M,[s[4]||(s[4]=e("span",null,"Order Discount:",-1)),e("span",null,"-"+t(o(n.order.general_discount)),1)])):c("",!0),e("div",R,[s[5]||(s[5]=e("span",null,"TOTAL:",-1)),e("span",null,t(o(n.order.total)),1)])]),e("div",z,[e("p",null,"Payment Method: "+t(n.order.payment_method),1),e("p",null,"Amount Tendered: "+t(o(n.order.amount_tendered)),1),n.order.change_due>0?(a(),r("p",B,"Change Due: "+t(o(n.order.change_due)),1)):c("",!0)]),e("div",V,[s[6]||(s[6]=e("p",null,"Thank you for your purchase!",-1)),e("p",null,t(u(b)),1)])],512)]),e("div",{class:"mt-4 flex justify-end"},[e("button",{onClick:p,class:"bg-teal-800 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition"}," Print Invoice ")])])]))}};export{Y as default};
