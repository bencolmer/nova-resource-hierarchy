<template>
  <div>
    <Head :title="title" />

    <Heading class="mb-6">{{ title }}</Heading>

    <Card
      class="flex flex-col items-center justify-center"
      style="min-height: 300px"
    >
      <vue-nestable
        keyProp="id"
        :maxDepth="10"
        :value="hierarchy"
        :hooks="{ beforeMove: this.beforeMove }"
        @input="hierarchy = $event"
        @change="handleChange"
      >
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
    enableOrdering: Boolean ,
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

    if (this.saveCanceller !== null) this.saveCanceller();
  },

  /**
   * Unbind the component listeners when the before component is destroyed
   */
  beforeUnmount() {
    if (this.saveCanceller !== null) this.saveCanceller();
  },

  data() {
    return {
      loading: false,
      isSaving: false,
      total: 0,
      hierarchy: [],
      saveCanceller: null,
    };
  },

  methods: {
    /**
    * Determine if an item can be reordered.
    */
    beforeMove(dragItem, pathFrom, pathTo) {
      // disable ordering while saving changes
      if (this.isSaving) return false;

      return this.enableOrdering;
    },

    /**
    * Handle change (triggered when a user drops an item).
    */
    handleChange(value, options) {
      if (! options?.items?.length) return;

      this.isSaving = true;

      Nova.request()
        .patch(
          `/nova-vendor/nova-resource-hierarchy/${this.resourceUriKey}`,
          { hierarchy: options.items },
          {
            cancelToken: new CancelToken(canceller => {
              this.saveCanceller = canceller
            }),
          }
        )
        .then(({ data }) => {
          this.isSaving = false;

          Nova.success(this.__('Successfully updated hierarchy'));
        })
        .catch((e) => {
          if (isCancel(e)) return;

          this.isSaving = false;

          // display error and refresh hierarchy
          Nova.error(this.__('Failed to save hierarchy'));
          this.getResources();

          throw e;
        });
    },

    /**
    * Get the resources based on the tool's resource.
    */
    getResources() {
      if (! this.resourceUriKey) return;

      this.loading = true;

      this.$nextTick(() => {
        Nova.request()
          .get(`/nova-vendor/nova-resource-hierarchy/${this.resourceUriKey}`, {
            cancelToken: new CancelToken(canceller => {
              this.canceller = canceller
            }),
          })
          .then(({ data }) => {
            this.loading = false;

            this.total = data.total,
            this.hierarchy = data.hierarchy;
          })
          .catch((e) => {
            if (isCancel(e)) return;

            this.loading = false;
            Nova.error(this.__('Failed to fetch hierarchy'));
            throw e;
          });
      });
    }
  },
}
</script>

<style>
.nestable {
  position: relative;
  padding: 1rem;
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
