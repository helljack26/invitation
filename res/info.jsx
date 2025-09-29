import { useEffect } from 'react';

export const isTouch = () => {
  useEffect(() => {
    if (typeof window === 'undefined') {
      return
    } else {
      return /Android|webOS|iPhone|iPod|iPad|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)
    }
  }, []);

}
