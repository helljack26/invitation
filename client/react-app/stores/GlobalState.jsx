import { observable, runInAction } from 'mobx'

const GlobalState = observable({
  locoScroll: null,
  scrollRef: null,
  scroll: null,
  isShowLeafFalling: false,
  isSideMenuOpen: false,

})

export default GlobalState;

