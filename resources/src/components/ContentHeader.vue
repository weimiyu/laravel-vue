<template>
  <div class="content-header">
    <slot name="title">
      <span>{{ name }}</span>
    </slot>
    <flex-spacer/>
    <collapse-button-group>
      <slot name="actions"/>
    </collapse-button-group>
  </div>
</template>

<script>
import _get from 'lodash/get'
import FlexSpacer from '@c/FlexSpacer'
import CollapseButtonGroup from '@c/CollapseButtonGroup'
import { mapState } from 'vuex'

export default {
  name: 'ContentHeader',
  components: {
    CollapseButtonGroup,
    FlexSpacer,
  },
  computed: {
    ...mapState({
      matchedMenusChain: (state) => state.matchedMenusChain,
    }),
    name() {
      let title = ''
      if (this.matchedMenusChain.length) {
        title = _get(this.matchedMenusChain, '0.title', '')
      }

      return title || _get(this.$route, 'meta.title', '')
    },
  },
}
</script>

<style scoped lang="scss">
.el-card__header {
  .content-header {
    margin-top: -20px;
    margin-bottom: -20px;
    padding: 0;
  }
}

.content-header {
  height: 60px;
  display: flex;
  align-items: center;
  padding: 0 20px;
}
</style>
