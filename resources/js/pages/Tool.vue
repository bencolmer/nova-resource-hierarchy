<template>
  <div>
    <Head :title="title" />

    <Heading>{{ title }}</Heading>

    <div class="nrh-hierarchy-header">
      <p class="nrh-intro-copy">
        Lorem ipsum dolor, sit amet consectetur adipisicing elit. Fugiat iste esse dolor provident cupiditate, repellendus illum laudantium!
      </p>

      <Link
        :href="createUrl"
        class="nrh-btn-primary"
      >
        Create
      </Link>
    </div>

    <Card class="nrh-hierarchy-list-card">
      <HierarchyList
        :resourceUriKey="resourceUriKey"
        :enableOrdering="enableOrdering"
      />
    </Card>
  </div>
</template>

<script>
export default {
  props: {
    resourceUriKey: {
      type: String,
      required: true
    },
    title: {
      type: String,
      required: false
    },
    enableOrdering: {
      type: Boolean,
      required: false,
      default: true
    }
  },

  computed: {
    createUrl() {
      return this.$url(
        `/resources/${this.resourceUriKey}/new`
      );
    }
  }
}
</script>

<style lang="scss">
.nrh-hierarchy-header {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  align-items: start;
  gap: 1rem;
  margin-top: 0.75rem;

  .nrh-btn-primary {
    margin-top: auto;
    margin-left: auto;
  }
}

.nrh-intro-copy {
  line-height: 1.25;
}

.nrh-hierarchy-list-card {
  padding: 1rem;
  margin-top: 1.5rem;
  width: 100%;
  overflow-x: auto;
}

@media (width >= 48rem) {
  .nrh-hierarchy-header {
    flex-direction: row;
    gap: 4rem;
  }

  .nrh-hierarchy-list-card {
    padding: 1.5rem;
  }
}
</style>
