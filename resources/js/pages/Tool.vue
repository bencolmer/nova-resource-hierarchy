<template>
  <div>
    <Head :title="title" />

    <Heading class="mb-6">{{ title }}</Heading>

    <Card
      class="flex flex-col items-center justify-center"
      style="min-height: 300px"
    >
      <vue-nestable :value="hierarchy" @input="hierarchy = $event">
        <template v-slot="slot">
          <vue-nestable-handle>
            {{ slot.item.id }}
          </vue-nestable-handle>
        </template>
      </vue-nestable>
    </Card>
  </div>
</template>

<script>
import { CancelToken, isCancel } from 'axios'
import { VueNestable, VueNestableHandle } from 'vue3-nestable'

export default {
  props: {
    title: String,
    resourceUriKey: String,
  },

  components: {
    VueNestable,
    VueNestableHandle
  },

  /**
   * Mount the component and retrieve its initial data.
   */
  async created() {
    this.getResources();

    // Nova.$on('refresh-resources', this.getResources);
  },

  data() {
    return {
      loading: false,
      total: 0,
      hierarchy: [],
    };
  },

  methods: {
    /**
    * Get the resources based on the tool's resource.
    */
    getResources() {
      if (! this.resourceUriKey) return;

      this.loading = true;

      this.$nextTick(() => {
        Nova.request().get(`/nova-vendor/nova-resource-hierarchy/${this.resourceUriKey}`, {
          cancelToken: new CancelToken(canceller => {
            this.canceller = canceller
          }),
        })
          .then(({ data }) => {
            this.loading = false;

            this.total = data.total,
            this.hierarchy = data.hierarchy;
          })
          .catch(e => {
            if (isCancel(e)) return;

            this.loading = false;
            throw e;
          })
      });
    }
  },
}
</script>

<style>
.nestable {
  position: relative;
  padding-top: 1rem;
  padding-bottom: 1rem;
}
.nestable-rtl {
  direction: rtl;
}
.nestable .nestable-list {
  margin: 0;
  padding: 0 0 0 40px;
  list-style-type: none;
}
.nestable-rtl .nestable-list {
  padding: 0 40px 0 0;
}
.nestable > .nestable-list {
  padding: 0;
}
.nestable-item,
.nestable-item-copy {
  margin: 10px 0 0;
}
.nestable-item:first-child,
.nestable-item-copy:first-child {
  margin-top: 0;
}
.nestable-item .nestable-list,
.nestable-item-copy .nestable-list {
  margin-top: 10px;
}
.nestable-item {
  position: relative;
}
.nestable-item.is-dragging .nestable-list {
  pointer-events: none;
}
.nestable-item.is-dragging * {
  opacity: 0;
  filter: alpha(opacity=0);
}
.nestable-item.is-dragging:before {
  content: ' ';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(106, 127, 233, 0.274);
  border: 1px dashed rgb(73, 100, 241);
  -webkit-border-radius: 5px;
  border-radius: 5px;
}
.nestable-drag-layer {
  position: fixed;
  top: 0;
  left: 0;
  z-index: 100;
  pointer-events: none;
}
.nestable-rtl .nestable-drag-layer {
  left: auto;
  right: 0;
}
.nestable-drag-layer > .nestable-list {
  position: absolute;
  top: 0;
  left: 0;
  padding: 0;
  background-color: rgba(106, 127, 233, 0.274);
}
.nestable-rtl .nestable-drag-layer > .nestable-list {
  padding: 0;
}
.nestable [draggable="true"] {
  cursor: move;
}
.nestable-handle {
  display: inline;
}
</style>
