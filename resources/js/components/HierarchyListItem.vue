<template>
  <vue-nestable-handle
    title="Move"
    aria-label="Move"
  >
    <Icon name="arrows-pointing-out" type="solid" class="nrh-icon" />
  </vue-nestable-handle>

  <div class="nrh-details">
    <p>{{ item.id }}</p>

    <div class="nrh-detail-actions">
      <Link
        title="View"
        aria-label="View"
        :href="viewUrl"
        class="nrh-detail-action-btn"
      >
        <Icon name="eye" type="outline" class="nrh-icon" />
      </Link>

      <Link
        title="Edit"
        aria-label="Edit"
        :href="updateUrl"
        class="nrh-detail-action-btn"
      >
        <Icon name="pencil-alt" type="outline" class="nrh-icon" />
      </Link>

      <button
        title="Delete"
        aria-label="Delete"
        type="button"
        @click.prevent="$emit('confirmDelete', item)"
        class="nrh-detail-action-btn"
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
    }
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
