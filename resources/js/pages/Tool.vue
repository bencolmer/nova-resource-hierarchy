<template>
  <div>
    <Head :title="title" />

    <Heading>{{ title }}</Heading>

    <p class="nrh-intro-copy">
      Lorem ipsum dolor, sit amet consectetur adipisicing elit. Fugiat iste esse dolor provident cupiditate, repellendus illum laudantium!
    </p>

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
}
</script>

<style>
.nrh-intro-copy {
  margin-top: 0.75rem;
  line-height: 1.25;

}
.nrh-hierarchy-list-card {
  padding: 1rem;
  margin-top: 2rem;
  width: 100%;
  overflow-x: auto;
}

@media (width >= 48rem) {
  .nrh-hierarchy-list-card {
    padding: 1.5rem;
  }
}
</style>
