<template>
  <div class="nrh-tool">
    <Head :title="pageTitle" />

    <Heading>{{ pageTitle }}</Heading>

    <div class="nrh-hierarchy-header" v-if="pageDescription || (authorizedToCreate && enableCreateAction)">
      <p class="nrh-intro-copy" v-if="pageDescription">
        {{ pageDescription }}
      </p>

      <Link
        v-if="authorizedToCreate && enableCreateAction"
        :href="createUrl"
        class="nrh-btn-primary"
      >
        Create
      </Link>
    </div>

    <Card class="nrh-hierarchy-list-card">
      <HierarchyList
        :resourceUriKey="resourceUriKey"
        :idKey="idKey"
        :maxDepth="maxDepth"
        :createUrl="createUrl"
        :enableReordering="enableReordering"
        :enableRtl="enableRtl"
        :enableCreateAction="enableCreateAction"
        :enableViewAction="enableViewAction"
        :enableUpdateAction="enableUpdateAction"
        :enableDeleteAction="enableDeleteAction"
        :authorizedToCreate="authorizedToCreate"
        :authorizedToReorderHierarchy="authorizedToReorderHierarchy"
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
    idKey: {
      type: String,
      required: true
    },
    maxDepth: {
      type: Number,
      required: true
    },
    pageTitle: {
      type: String,
      required: false
    },
    pageDescription: {
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
      default: false
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
  margin-top: 1.5rem;
  width: 100%;
  overflow-x: auto;
}

@media (width >= 48rem) {
  .nrh-hierarchy-header {
    flex-direction: row;
    gap: 4rem;

    &:is([dir=rtl] *) {
      .nrh-btn-primary {
        margin-left: 0;
        margin-right: auto;
      }
    }
  }
}
</style>
