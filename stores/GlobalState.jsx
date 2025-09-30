// GlobalState.js
import { makeAutoObservable } from "mobx";

class GlobalState {
  locoScroll = 0;
  scroll = null;
  isSideMenuOpen = false; 

  constructor() {
    makeAutoObservable(this, {
      setScroll: true,    // mark as action
    });
  }

  setScroll(y, instance) {
    this.locoScroll = y;
    this.scroll = instance;
  }
}

export default new GlobalState();
