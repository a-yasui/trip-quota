<template>
  <div>
    <!-- 班グループ選択 -->
    <div v-if="branchGroups.length > 0" class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('班グループから選択') }}</label>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
        <div v-for="group in branchGroups" :key="group.id" class="flex items-center">
          <input
            type="checkbox"
            :id="'group_' + group.id"
            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
            v-model="selectedGroups[group.id]"
            @change="updateMembersByGroup(group)"
          >
          <label :for="'group_' + group.id" class="ml-2 block text-sm text-gray-900">
            {{ group.name }} ({{ group.members.length }}名)
          </label>
        </div>
      </div>
    </div>
    
    <!-- メンバー選択 -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
      <div v-for="member in members" :key="member.id" class="flex items-center">
        <input
          type="checkbox"
          :id="'member_' + member.id"
          :name="inputName + '[]'"
          :value="member.id"
          v-model="selectedMembers[member.id]"
          @change="updateGroupsByMember"
          class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
        >
        <label :for="'member_' + member.id" class="ml-2 block text-sm text-gray-900">
          {{ member.name }}
        </label>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    branchGroups: {
      type: Array,
      required: true
    },
    members: {
      type: Array,
      required: true
    },
    initialSelectedMembers: {
      type: Array,
      default: () => []
    },
    inputName: {
      type: String,
      default: 'member_ids'
    }
  },
  data() {
    return {
      selectedGroups: {},
      selectedMembers: {}
    }
  },
  created() {
    // 初期選択状態を設定
    this.members.forEach(member => {
      this.selectedMembers[member.id] = this.initialSelectedMembers.includes(Number(member.id));
    });
    
    // グループの初期状態を設定
    this.updateGroupsByMember();
  },
  methods: {
    // __ 関数の定義（Laravel Blade の __ 関数の代替）
    __(text) {
      return text; // 実際の翻訳機能が必要な場合は、ここを拡張
    },
    
    // 班グループが選択された時、所属メンバーを更新
    updateMembersByGroup(group) {
      console.log('updateMembersByGroup called', group);
      const isChecked = this.selectedGroups[group.id];
      console.log('isChecked:', isChecked);
      
      if (group.members && Array.isArray(group.members)) {
        group.members.forEach(member => {
          const memberId = typeof member === 'object' ? member.id : member;
          this.selectedMembers[memberId] = isChecked;
          console.log(`Member ${memberId} selected:`, this.selectedMembers[memberId]);
        });
      }
      
      // 追加: メンバー選択変更後に親コンポーネントに通知
      console.log('Calling updateGroupsByMember from updateMembersByGroup');
      this.updateGroupsByMember();
    },
    
    // メンバーが選択された時、班グループの状態を更新
    updateGroupsByMember() {
      console.log('updateGroupsByMember called');
      
      this.branchGroups.forEach(group => {
        if (group.members && Array.isArray(group.members)) {
          const allChecked = group.members.every(member => {
            const memberId = typeof member === 'object' ? member.id : member;
            return this.selectedMembers[memberId];
          });
          this.selectedGroups[group.id] = allChecked;
          console.log(`Group ${group.id} allChecked:`, allChecked);
        }
      });
      
      // 選択されたメンバーIDのリストを作成
      const selectedIds = Object.entries(this.selectedMembers)
        .filter(([_, isSelected]) => isSelected)
        .map(([id, _]) => Number(id));
      
      console.log('Selected member IDs:', selectedIds);
      
      // 親コンポーネントに選択変更を通知
      console.log('Emitting update:selected event');
      this.$emit('update:selected', selectedIds);
    }
  }
}
</script>
