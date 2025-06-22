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
        class="nrh-create-btn"
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
}

.nrh-intro-copy {
  line-height: 1.25;
}

.nrh-create-btn {
  display: flex;
  align-items: center;
  flex-shrink: 0;
  height: 2.25rem;
  padding-left: 1rem;
  padding-right: 1rem;
  margin-top: auto;
  margin-left: auto;
  font-weight: bold;
  border-radius: 0.25rem;
  font-size: 0.875rem;
  line-height: 1.25rem;
  color: white;
  background-color: rgba(var(--colors-primary-500));
  --btn-shadow: 0 1px 3px 0 rgba(0,0,0,0.1),0 1px 2px -1px rgba(0,0,0,0.1);
  box-shadow: 0 0 #000000,0 0 #000000, var(--btn-shadow);

  &:focus {
    outline: 2px solid transparent;
    outline-offset: 2px;

    box-shadow: 0 0 0 0px #ffffff, 0 0 0 3px rgba(var(--colors-primary-200)), var(--btn-shadow);
  }

  &:hover {
    background-color: rgba(var(--colors-primary-400));
  }

  &:active {
    background-color: rgba(var(--colors-primary-600));
  }

  &:is(.dark *) {
    color: rgba(var(--colors-gray-800));

    &:focus {
      box-shadow: 0 0 0 0px #ffffff, 0 0 0 3px rgba(var(--colors-gray-600)), var(--btn-shadow);
    }
  }
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
