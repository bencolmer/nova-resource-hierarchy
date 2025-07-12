<template>
  <VueNestableHandle
    v-if="authorizedToReorderHierarchy && enableReordering"
    :title="__('novaResourceHierarchy.move')"
    :aria-label="__('novaResourceHierarchy.move')"
    :class="isDisabled ? 'nrh-disabled' : ''"
  >
    <Icon name="arrows-pointing-out" type="solid" class="nrh-icon" />
  </VueNestableHandle>

  <div class="nrh-details">
    <p>{{ item.title }}</p>

    <div
      v-if="enableViewAction || enableUpdateAction || enableDeleteAction"
      :class="`nrh-detail-actions ${isDisabled ? 'nrh-disabled' : ''}`"
    >
      <Link
        v-if="enableViewAction"
        :title="__('novaResourceHierarchy.view')"
        :aria-label="__('novaResourceHierarchy.view')"
        :href="viewUrl"
        :class="`nrh-detail-action ${!item?.authorizedToView ? 'nrh-unauthorized' : ''}`"
        :tabIndex="!item?.authorizedToView || isDisabled ? '-1' : ''"
      >
        <Icon name="eye" type="outline" class="nrh-icon" />
      </Link>

      <Link
        v-if="enableUpdateAction"
        :title="__('novaResourceHierarchy.edit')"
        :aria-label="__('novaResourceHierarchy.edit')"
        :href="updateUrl"
        :class="`nrh-detail-action ${!item?.authorizedToUpdate ? 'nrh-unauthorized' : ''}`"
        :tabIndex="!item?.authorizedToUpdate || isDisabled ? '-1' : ''"
      >
        <Icon name="pencil-alt" type="outline" class="nrh-icon" />
      </Link>

      <button
        v-if="enableDeleteAction"
        :title="__('novaResourceHierarchy.delete')"
        :aria-label="__('novaResourceHierarchy.delete')"
        type="button"
        @click.prevent="$emit('confirmDelete', item)"
        :class="`nrh-detail-action ${!item?.authorizedToDelete ? 'nrh-unauthorized' : ''}`"
        :tabIndex="!item?.authorizedToDelete || isDisabled ? '-1' : ''"
        :disabled="!item?.authorizedToDelete || isDisabled"
      >
        <Icon name="trash" type="outline" class="nrh-icon" />
      </button>
    </div>
  </div>
</template>

<script>
import { Icon } from 'laravel-nova-ui'
import { VueNestableHandle } from 'vue3-nestable'

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
    item: {
      type: Object,
      required: true,
    },
    authorizedToReorderHierarchy: {
      type: Boolean,
      required: false,
      default: false
    },
    isDisabled: {
      type: Boolean,
      default: false,
      required: false,
    },
    enableReordering: {
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

  emits: ['confirmDelete'],

  components: {
    Icon,
    VueNestableHandle,
  },

  computed: {
    viewUrl() {
      return this.$url(
        `/resources/${this.resourceUriKey}/${this.item?.[this.idKey]}`
      );
    },

    updateUrl() {
      return this.$url(
        `/resources/${this.resourceUriKey}/${this.item?.[this.idKey]}/edit`
      );
    },
  },
}
</script>
