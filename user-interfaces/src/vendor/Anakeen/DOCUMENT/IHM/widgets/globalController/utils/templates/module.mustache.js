export default `
import {{key}}Function from "{{{path}}}";
window.ank.smartElement.globalController._registerScript("{{{path}}}", {{key}}Function);
`;
