/**
 * debouncing is a technique of keeping the trigger rate at exactly 0
 * until a period of calm, and then triggering the listener exactly once
 * @param delay in ms
 * @param fn callback to call after delay
 */
export function debounced(delay: number, fn) {
  let timerId;
  return (...args) => {
    if (timerId) {
      clearTimeout(timerId);
    }
    timerId = setTimeout(() => {
      fn(...args);
      timerId = null;
    }, delay);
  };
}
