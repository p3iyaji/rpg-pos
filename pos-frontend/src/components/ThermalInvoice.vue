<script setup>
import { ref, defineProps, defineExpose } from 'vue';

const props = defineProps({
  order: {
    type: Object,
    required: true
  },
  businessInfo: {
    type: Object,
    default: () => ({
      name: 'RPG-POS',
      address: '123 Main St, City',
      phone: '0800-123-4567',
      footer: 'Returns within 7 days with receipt'
    })
  }
});

const invoiceContent = ref(null);

const formattedDate = () => new Date(props.order.date).toLocaleString();
const businessName = props.businessInfo.name || 'RPG-POS';
const businessAddress = props.businessInfo.address || '';
const businessPhone = props.businessInfo.phone || '';
const businessFooter = props.businessInfo.footer || 'Thank you for your business!';

const formatCurrency = (amount) => {
  return `₦${parseFloat(amount).toFixed(2)}`;
};

const printInvoice = () => {
  if (!invoiceContent.value) {
    console.error('Invoice content not found');
    return;
  }

  const printWindow = window.open('', '_blank');
  printWindow.document.write(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>Invoice #${props.order.order_no}</title>
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
      ${invoiceContent.value.innerHTML}
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
  `);
  printWindow.document.close();
};

// Expose the print method to parent components
defineExpose({
  printInvoice
});
</script>

<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-bold">Invoice Preview</h3>
        <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700">
          ✕
        </button>
      </div>

      <div class="max-h-[70vh] overflow-y-auto">
        <div ref="invoiceContent" class="thermal-invoice p-4">
          <!-- Invoice content -->
          <div class="invoice-header">
            <h2>{{ businessName }}</h2>
            <p>{{ businessAddress }}</p>
            <p>{{ businessPhone }}</p>
            <p>Order #: {{ order.order_no }}</p>
            <p>Date: {{ formattedDate() }}</p>
          </div>

          <div class="customer-info">
            <p>Customer: {{ order.customer.name }}</p>
            <p v-if="order.customer.phone">Phone: {{ order.customer.phone }}</p>
          </div>

          <div class="items-header">
            <span class="item-name">ITEM</span>
            <span class="item-qty">QTY</span>
            <span class="item-price">PRICE</span>
            <span class="item-total">TOTAL</span>
          </div>

          <div class="items-list">
            <div v-for="item in order.items" :key="item.id" class="item-row">
              <span class="item-name">{{ item.name || `Product ${item.product_id}` }}</span>
              <span class="item-qty">{{ item.quantity }}</span>
              <span class="item-price">{{ formatCurrency(item.unit_price) }}</span>
              <span class="item-total">{{ formatCurrency(item.total) }}</span>
            </div>
          </div>

          <div class="summary">
            <div class="summary-row">
              <span>Subtotal:</span>
              <span>{{ formatCurrency(order.subtotal) }}</span>
            </div>
            <div v-if="order.product_discounts > 0" class="summary-row">
              <span>Product Discounts:</span>
              <span>-{{ formatCurrency(order.product_discounts) }}</span>
            </div>
            <div v-if="order.general_discount > 0" class="summary-row">
              <span>Order Discount:</span>
              <span>-{{ formatCurrency(order.general_discount) }}</span>
            </div>
            <div class="summary-row total">
              <span>TOTAL:</span>
              <span>{{ formatCurrency(order.total) }}</span>
            </div>
          </div>

          <div class="payment-info">
            <p>Payment Method: {{ order.payment_method }}</p>
            <p>Amount Tendered: {{ formatCurrency(order.amount_tendered) }}</p>
            <p v-if="order.change_due > 0">Change Due: {{ formatCurrency(order.change_due) }}</p>
          </div>

          <div class="footer">
            <p>Thank you for your purchase!</p>
            <p>{{ businessFooter }}</p>
          </div>
        </div>
      </div>

      <div class="mt-4 flex justify-end">
        <button @click="printInvoice" class="bg-teal-800 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition">
          Print Invoice
        </button>
      </div>
    </div>
  </div>
</template>