<template>
  <VueNestableHandle
    v-if="enableReordering"
    title="Move"
    aria-label="Move"
    :class="isDisabled ? 'nrh-disabled' : ''"
  >
    <Icon name="arrows-pointing-out" type="solid" class="nrh-icon" />
  </VueNestableHandle>

  <div class="nrh-details">
    <p>{{ item.id }}</p>

    <div
      v-if="enableViewAction || enableUpdateAction || enableDeleteAction"
      :class="`nrh-detail-actions ${isDisabled ? 'nrh-disabled' : ''}`"
    >
      <Link
        v-if="enableViewAction"
        title="View"
        aria-label="View"
        :href="viewUrl"
        class="nrh-detail-action"
        :tabIndex="isDisabled ? '-1' : ''"
      >
        <Icon name="eye" type="outline" class="nrh-icon" />
      </Link>

      <Link
        v-if="enableUpdateAction"
        title="Edit"
        aria-label="Edit"
        :href="updateUrl"
        class="nrh-detail-action"
        :tabIndex="isDisabled ? '-1' : ''"
      >
        <Icon name="pencil-alt" type="outline" class="nrh-icon" />
      </Link>

      <button
        v-if="enableDeleteAction"
        title="Delete"
        aria-label="Delete"
        type="button"
        @click.prevent="$emit('confirmDelete', item)"
        class="nrh-detail-action"
        :tabIndex="isDisabled ? '-1' : ''"
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
    item: {
      type: Object,
      required: true,
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
        `/resources/${this.resourceUriKey}/${this.item?.id}`
      );
    },

    updateUrl() {
      return this.$url(
        `/resources/${this.resourceUriKey}/${this.item?.id}/edit`
      );
    },
  },
}
</script>
