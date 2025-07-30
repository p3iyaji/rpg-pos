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
          font-size: 16px;
          width: 80mm;
          margin: 0;
          padding: 2px;
        }
        .thermal-invoice {
          width: 100%;
        }
        .invoice-header, .footer {
          text-align: center;
          margin-bottom: 10px;
        }
        .invoice-header h2 {
          font-size: 16px;
          margin: 2px 0;
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
          font-size: 12px;
        }
        .item-qty {
          width: 5%;
          text-align: right;
          font-size: 12px;
        }
        .item-price {
          width: 25%;
          text-align: right;
          font-size: 12px;
        }
        .item-total {
          width: 30%;
          text-align: right;
          font-size: 12px;
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
        <!-- Add thermal styles to the preview container -->
        <div ref="invoiceContent" class="thermal-invoice p-4"
          style="font-family: 'Courier New', monospace; font-size: 12px; width: 80mm;">
          <!-- Invoice header with thermal styling -->
          <div class="invoice-header" style="text-align: center; margin-bottom: 10px;">
            <h2 style="font-size: 14px; margin: 5px 0;">{{ businessName }}</h2>
            <p>{{ businessAddress }}</p>
            <p>{{ businessPhone }}</p>
            <p style="font-weight: bold;">Order #: {{ order.order_no }}</p>
            <p>Date: {{ formattedDate() }}</p>
          </div>

          <!-- Customer info -->
          <div class="customer-info" style="margin: 5px 0;">
            <p>Customer: {{ order.customer.name }}</p>
            <p v-if="order.customer.phone">Phone: {{ order.customer.phone }}</p>
          </div>

          <!-- Items header with thermal styling -->
          <div class="items-header" style="display: flex; justify-content: space-between; margin-bottom: 3px;">
            <span class="item-name" style="width: 40%; overflow: hidden; text-overflow: ellipsis;">ITEM</span>
            <span class="item-qty" style="width: 5%; text-align: right;">QTY</span>
            <span class="item-price" style="width: 25%; text-align: right;">PRICE</span>
            <span class="item-total" style="width: 30%; text-align: right;">TOTAL</span>
          </div>

          <!-- Items list -->
          <div class="items-list">
            <div v-for="item in order.items" :key="item.id" class="item-row"
              style="display: flex; justify-content: space-between; margin-bottom: 3px;">
              <span class="item-name" style="width: 40%; overflow: hidden; text-overflow: ellipsis;">{{ item.name ||
                `Product ${item.product_id}` }}</span>
              <span class="item-qty" style="width: 5%; text-align: right;">{{ item.quantity }}</span>
              <span class="item-price" style="width: 25%; text-align: right;">{{ formatCurrency(item.unit_price)
              }}</span>
              <span class="item-total" style="width: 30%; text-align: right;">{{ formatCurrency(item.total) }}</span>
            </div>
          </div>

          <!-- Summary with thermal styling -->
          <div class="summary" style="margin-top: 10px; border-top: 1px dashed #000; padding-top: 5px;">
            <div class="summary-row" style="display: flex; justify-content: space-between; margin-bottom: 3px;">
              <span>Subtotal:</span>
              <span>{{ formatCurrency(order.subtotal) }}</span>
            </div>
            <div v-if="order.product_discounts > 0" class="summary-row"
              style="display: flex; justify-content: space-between; margin-bottom: 3px;">
              <span>Product Discounts:</span>
              <span>-{{ formatCurrency(order.product_discounts) }}</span>
            </div>
            <div v-if="order.general_discount > 0" class="summary-row"
              style="display: flex; justify-content: space-between; margin-bottom: 3px;">
              <span>Order Discount:</span>
              <span>-{{ formatCurrency(order.general_discount) }}</span>
            </div>
            <div class="summary-row total"
              style="display: flex; justify-content: space-between; font-weight: bold; border-top: 1px dashed #000; padding-top: 5px;">
              <span>TOTAL:</span>
              <span>{{ formatCurrency(order.total) }}</span>
            </div>
          </div>

          <!-- Payment info -->
          <div class="payment-info" style="margin: 5px 0;">
            <p>Payment Method: {{ order.payment_method }}</p>
            <p>Amount Tendered: {{ formatCurrency(order.amount_tendered) }}</p>
            <p v-if="order.change_due > 0">Change Due: {{ formatCurrency(order.change_due) }}</p>
          </div>

          <!-- Footer -->
          <div class="footer" style="text-align: center; margin-bottom: 10px;">
            <!-- <p>Thank you for your purchase!</p> -->
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