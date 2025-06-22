<template>
  <vue-nestable
    keyProp="id"
    :maxDepth="10"
    :value="hierarchy"
    :hooks="{ beforeMove: this.beforeMove }"
    class="nrh-nestable"
    :rtl="false"
    @input="hierarchy = $event"
    @change="handleChange"
  >
    <template v-slot="slot">
      <HierarchyListItem
        :item="slot.item"
        :resourceUriKey="resourceUriKey"
        @confirmDelete="confirmDelete"
      />
    </template>
  </vue-nestable>
</template>

<script>
import { CancelToken, isCancel } from 'axios'
import { VueNestable } from 'vue3-nestable'

export default {
  props: {
    resourceUriKey: {
      type: String,
      required: true
    },
    enableOrdering: {
      type: Boolean,
      required: false,
      default: true
    },
  },

  components: {
    VueNestable,
  },

  /**
   * Mount the component and retrieve its initial data.
   */
  async created() {
    this.getResources();

    if (this.loadCanceller !== null) this.loadCanceller();
    if (this.saveCanceller !== null) this.saveCanceller();
    if (this.deleteCanceller !== null) this.deleteCanceller();
  },

  /**
   * Unbind the component listeners when the before component is destroyed
   */
  beforeUnmount() {
    if (this.loadCanceller !== null) this.loadCanceller();
    if (this.saveCanceller !== null) this.saveCanceller();
    if (this.deleteCanceller !== null) this.deleteCanceller();
  },

  data() {
    return {
      // loading states
      isLoading: false,
      isSaving: false,

      // resource data
      total: 0,
      hierarchy: [],

      // cancel tokens
      loadCanceller: null,
      saveCanceller: null,
      deleteCanceller: null,
    };
  },

  methods: {
    /**
    * Determine if an item can be reordered.
    */
    beforeMove(dragItem, pathFrom, pathTo) {
      // disable ordering while saving changes or loading
      if (this.isLoading || this.isSaving) return false;

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
              this.saveCanceller = canceller;
            }),
          }
        )
        .then(({ data }) => {
          this.isSaving = false;

          if (! data?.success) {
            Nova.error(this.__('Failed to save hierarchy'));
            this.getResources();
            return;
          }

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

      this.isLoading = true;

      this.$nextTick(() => {
        Nova.request()
          .get(`/nova-vendor/nova-resource-hierarchy/${this.resourceUriKey}`, {
            cancelToken: new CancelToken(canceller => {
              this.loadCanceller = canceller;
            }),
          })
          .then(({ data }) => {
            this.isLoading = false;

            this.total = data.total,
            this.hierarchy = data.hierarchy;
          })
          .catch((e) => {
            if (isCancel(e)) return;

            this.isLoading = false;
            Nova.error(this.__('Failed to fetch hierarchy'));
            throw e;
          });
      });
    },

    /**
    * Confirm a delete action.
    */
    confirmDelete(item) {
      if (confirm('Are you sure you want to delete this resource?')) {
        this.deleteResource(item);
      }
    },

    /**
    * Delete a resource.
    */
    deleteResource(item) {
      if (! item?.id) return;

      this.isSaving = true;

      Nova.request()
        .delete(
          `/nova-api/${this.resourceUriKey}?resources[]=${item.id}`,
          {
            cancelToken: new CancelToken(canceller => {
              this.deleteCanceller = canceller;
            }),
          }
        )
        .then(({ data }) => {
          this.isSaving = false;

          // refetch resources after delete
          this.getResources();
          Nova.success('Successfully deleted the item');
        })
        .catch((e) => {
          if (isCancel(e)) return;

          this.isSaving = false;

          this.getResources();
          Nova.error(this.__('Failed to delete the item'));
          throw e;
        });
    },
  },
}
</script>

<style lang="scss">
.nrh-nestable {
  &.nestable {
    position: relative;
    width: fit-content;
    min-width: 100%;

    .nestable-list {
      margin: 0;
      padding: 0 0 0 3rem;
      list-style-type: none;
    }

    & > .nestable-list {
      padding: 0;
    }

    [draggable="true"] {
      cursor: move;
    }
  }

  .nestable-item,
  .nestable-item-copy {
    margin: 10px 0 0;

    &:first-child {
      margin-top: 0;
    }

    .nestable-list {
      margin-top: 10px;
    }
  }

  .nestable-item {
    position: relative;

    &.is-dragging {
      .nestable-list {
        pointer-events: none;
      }

      * {
        opacity: 0;
        filter: alpha(opacity=0);
      }

      &:before {
        content: ' ';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(var(--colors-primary-500), 0.074);
        border: 1px dashed rgba(var(--colors-primary-500));
        -webkit-border-radius: 5px;
        border-radius: 5px;
      }
    }
  }

  .nestable-drag-layer {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 100;
    pointer-events: none;

    & > .nestable-list {
      position: absolute;
      top: 0;
      left: 0;
      padding: 0;
      background-color: rgba(var(--colors-primary-500), 0.074);
      border-radius: 0.5rem;

      .nestable-item-content {
        .nrh-details {
          border-color: rgba(var(--colors-gray-300));
        }

        .nestable-handle {
          background-color: rgba(255, 255, 255, 0);
          border-color: rgba(var(--colors-gray-300));
        }
      }
    }
  }

  .nestable-item-content {
    display: flex;
    justify-content: start;
    align-items: center;

    .nrh-details {
      display: flex;
      justify-content: space-between;
      align-items: center;
      width: 100%;
      min-width: fit-content;
      gap: 0.5rem;
      padding: 0.75rem;
      border-color: rgba(var(--colors-gray-200));
      border-style: solid;
      border-width: 1px;
      border-radius: 0 0.375rem 0.375rem 0;

      .nrh-detail-actions {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;

        .nrh-detail-action-btn {
          color: rgba(var(--colors-gray-500));

          .nrh-icon {
            height: 1.375rem;
            width: 1.375rem;
          }

          &:hover {
            color: rgba(var(--colors-primary-500));
          }
        }
      }
    }

    .nestable-handle {
      display: flex;
      justify-content: center;
      align-items: center;
      align-self: stretch;
      padding: 0.5rem;
      padding-right: 0.45rem;
      border-color: rgba(var(--colors-gray-200));
      border-style: dashed;
      border-right: none;
      border-width: 1px;
      border-radius: 0.375rem 0 0 0.375rem;
      background-color: rgba(var(--colors-gray-100));
      opacity: 0.65;
      transition: opacity 0.1s;

      .nrh-icon {
        height: 1.25rem;
        width: 1.25rem;
      }
    }

    &:hover .nestable-handle {
      opacity: 1;
    }
  }

  // rtl support
  &.nestable-rtl {
    direction: rtl;

    .nestable-list {
      padding: 0 3rem 0 0;
    }

    & > .nestable-list {
      padding: 0;
    }

    .nestable-drag-layer {
      left: auto;
      right: 0;
    }

    .nestable-drag-layer > .nestable-list {
      padding: 0;
    }

    .nestable-item-content {
      .nrh-details {
        border-radius: 0.375rem 0 0 0.375rem;
      }

      .nestable-handle {
        border-radius: 0 0.375rem 0.375rem 0;
      }
    }
  }
}
</style>
