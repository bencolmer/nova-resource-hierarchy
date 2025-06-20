import Tool from './pages/Tool'
import HierarchyList from './components/HierarchyList'

Nova.inertia('ResourceHierarchy', Tool);

Nova.booting((app, store) => {
  app.component('HierarchyList', HierarchyList);
});
