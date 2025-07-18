<template>
  <LoadingView :loading="isLoading">
    <!-- No results found -->
    <div
      v-if="!hierarchy?.length"
      class="nrh-no-results"
    >
      <Heading :level="2">
        {{ __('novaResourceHierarchy.noResourcesFound') }}
      </Heading>

      <p>{{ __('novaResourceHierarchy.noResourcesToDisplay') }}</p>

      <Link
        v-if="authorizedToCreate && enableCreateAction && createUrl"
        :href="createUrl"
        class="nrh-btn-primary"
      >
        {{ __('novaResourceHierarchy.create') }}
      </Link>
    </div>

    <!-- Result list -->
    <VueNestable
      v-if="hierarchy?.length > 0"
      :keyProp="idKey"
      :maxDepth="maxDepth"
      :value="hierarchy"
      :hooks="{ beforeMove: this.beforeMove }"
      class="nrh-nestable"
      :rtl="enableRtl"
      @input="hierarchy = $event"
      @change="handleChange"
    >
      <template v-slot="slot">
        <HierarchyListItem
          :item="slot.item"
          :idKey="idKey"
          :resourceUriKey="resourceUriKey"
          :isDisabled="isLoading || isSaving"
          :enableReordering="enableReordering"
          :enableViewAction="enableViewAction"
          :enableUpdateAction="enableUpdateAction"
          :enableDeleteAction="enableDeleteAction"
          :authorizedToReorderHierarchy="authorizedToReorderHierarchy"
          @confirmDelete="confirmDelete"
        />
      </template>
    </VueNestable>
  </LoadingView>
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
    idKey: {
      type: String,
      required: true
    },
    maxDepth: {
      type: Number,
      required: true
    },
    createUrl: {
      type: String,
      required: false
    },
    authorizedToCreate: {
      type: Boolean,
      required: false,
      default: false
    },
    authorizedToReorderHierarchy: {
      type: Boolean,
      required: false,
      default: false
    },
    enableReordering: {
      type: Boolean,
      required: false,
      default: true
    },
    enableRtl: {
      type: Boolean,
      required: false,
      default: false
    },
    enableCreateAction: {
      type: Boolean,
      required: false,
      default: false
    },
    enableViewAction: {
      type: Boolean,
      required: false,
      default: false
    },
    enableUpdateAction: {
      type: Boolean,
      required: false,
      default: false
    },
    enableDeleteAction: {
      type: Boolean,
      required: false,
      default: false
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

      return this.authorizedToReorderHierarchy && this.enableReordering;
    },

    /**
    * Handle change (triggered when a user drops an item).
    */
    handleChange(value, options) {
      if (this.isSaving) return;
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
            Nova.error(this.__('novaResourceHierarchy.failedToSaveHierarchy'));
            this.getResources();
            return;
          }

          Nova.success(this.__('novaResourceHierarchy.updatedHierarchy'));
        })
        .catch((e) => {
          if (isCancel(e)) return;

          this.isSaving = false;

          // display error and refresh hierarchy
          Nova.error(this.__('novaResourceHierarchy.failedToSaveHierarchy'));
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

            this.total = data.total;
            this.hierarchy = data.hierarchy;
          })
          .catch((e) => {
            if (isCancel(e)) return;

            this.isLoading = false;
            Nova.error(this.__('novaResourceHierarchy.failedToFetchHierarchy'));
            throw e;
          });
      });
    },

    /**
    * Confirm a delete action.
    */
    confirmDelete(item) {
      if (confirm(this.__('novaResourceHierarchy.confirmDelete'))) {
        this.deleteResource(item);
      }
    },

    /**
    * Delete a resource.
    */
    deleteResource(item) {
      if (! item?.[this.idKey]) return;

      this.isSaving = true;

      Nova.request()
        .delete(
          `/nova-api/${this.resourceUriKey}?resources[]=${item[this.idKey]}`,
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
          Nova.success(this.__('novaResourceHierarchy.deletedResource'));
        })
        .catch((e) => {
          if (isCancel(e)) return;

          this.isSaving = false;

          this.getResources();
          Nova.error(this.__('novaResourceHierarchy.failedToDelete'));
          throw e;
        });
    },
  },
}
</script>

<style lang="scss">
.nrh-no-results {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  gap: 0.5rem;
  min-height: 12rem;
  padding: 1rem;
  margin: auto;

  h2 {
    font-size: 1.25rem;
    line-height: 1.75rem;
  }
}

.nrh-nestable {
  &.nestable {
    position: relative;
    width: fit-content;
    min-width: 100%;
    padding: 1rem;

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

          &:is(.dark *) {
            border-color: rgba(var(--colors-gray-600));
          }
        }

        .nestable-handle {
          background-color: rgba(255, 255, 255, 0.125);
          border-color: rgba(var(--colors-gray-300));

          &:is(.dark *) {
            border-color: rgba(var(--colors-gray-600));
          }
        }
      }
    }
  }

  .nestable-item-content {
    display: flex;
    justify-content: start;
    align-items: center;

    &:is(.dark *):hover .nrh-details {
      background-color: rgba(var(--colors-gray-900));
    }

    &:hover .nrh-details {
      background-color: rgba(var(--colors-gray-50));
    }

    .nrh-details {
      display: flex;
      justify-content: space-between;
      align-items: center;
      width: 100%;
      gap: 0.5rem;
      padding: 0.75rem;
      border-color: rgba(var(--colors-gray-200));
      border-style: solid;
      border-width: 1px;
      border-radius: 0.375rem;
      font-weight: 600;

      &:is(.dark *) {
        border-color: rgba(var(--colors-gray-700));
      }

      .nrh-detail-actions {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.125rem;

        &.nrh-disabled {
          cursor: not-allowed;

          .nrh-detail-action {
            pointer-events: none;
            opacity: 0.5;
          }
        }

        .nrh-detail-action {
          padding: 0.125rem;
          border-radius: 0.25rem;
          color: rgba(var(--colors-gray-500));

          &.nrh-unauthorized {
            pointer-events: none;
            opacity: 0.5;
          }

          &:is(.dark *) {
            color: rgba(var(--colors-gray-400));

            &:focus {
              box-shadow: 0 0 0 0px #ffffff, 0 0 0 3px rgba(var(--colors-gray-600)), var(--nrh-btn-shadow);
            }
          }

          &:focus {
            outline: 2px solid transparent;
            outline-offset: 2px;
            box-shadow: 0 0 0 0px #ffffff, 0 0 0 3px rgba(var(--colors-primary-200)), var(--nrh-btn-shadow);
          }

          &:hover {
            color: rgba(var(--colors-primary-500));
          }

          .nrh-icon {
            height: 1.5rem;
            width: 1.5rem;
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

      & + .nrh-details {
        border-radius: 0 0.375rem 0.375rem 0;
      }

      &.nrh-disabled {
        pointer-events: none;
        opacity: 0.4;
      }

      &:is(.dark *) {
        border-color: rgba(var(--colors-gray-700));
        background-color: rgba(var(--colors-gray-600));

        .nrh-icon {
          color: rgba(var(--colors-gray-200));
        }
      }

      &:hover {
        opacity: 1;
      }

      .nrh-icon {
        height: 1.375rem;
        width: 1.375rem;
        color: rgba(var(--colors-gray-600));
      }
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

@media (width >= 48rem) {
  .nrh-no-results, .nrh-nestable.nestable {
    padding: 1.5rem;
  }
}
</style>
