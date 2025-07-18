import Tool from './pages/Tool'
import HierarchyList from './components/HierarchyList'
import HierarchyListItem from './components/HierarchyListItem'

Nova.inertia('ResourceHierarchy', Tool);

Nova.booting((app, store) => {
  app.component('HierarchyList', HierarchyList);
  app.component('HierarchyListItem', HierarchyListItem);
});
