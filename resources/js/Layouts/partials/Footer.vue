<script setup>
import { useTemplateStore } from "@/stores/template";

// Main store
const store = useTemplateStore();
</script>

<template>
  <!-- Footer -->
  <footer id="page-footer">
    <slot>
      <div class="bg-body-light">
        <div class="content py-3">
          <div class="row fs-sm">
            <div class="col-sm-4 order-sm-3 py-1 text-center text-sm-end">
              <slot name="content-right">
                Made in
                <a
                  class="fw-semibold"
                  href="https://www.medialm.it"
                  target="_blank"
                  >MEDIALM</a
                >
              </slot>
            </div>
            <div class="col-sm-4 order-sm-1 py-1 text-center text-sm-start">
              <slot name="content-left">
                <span
                  class="fw-semibold"
                  href="https://pixelcave.com/products/oneui-vue-edition"
                  >
                  {{ store.app.name + " " + store.app.version }}
                  </span>
                &copy; {{ store.app.copyright }}
              </slot>
            </div>
            <div class="col-md-4 py-1 text-center order-sm-2">
              <slot class="content-center">
                P.IVA: <span class="fw-semibold">06418130826</span>
              </slot>
            </div>
          </div>
        </div>
      </div>
    </slot>
  </footer>
  <!-- END Footer -->
</template>
