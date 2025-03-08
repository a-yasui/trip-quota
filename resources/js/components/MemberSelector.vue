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
          :checked="selectedMembers[member.id]"
          @change="toggleMember(member.id, $event)"
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
    // デバッグ情報：グループとメンバーの一覧
    console.log('=== デバッグ情報：起動時 ===');
    console.log('グループ一覧:', this.branchGroups.map(group => ({ id: group.id, name: group.name, members: group.members.map(m => typeof m === 'object' ? m.id : m) })));
    console.log('メンバー一覧:', this.members.map(member => ({ id: member.id, name: member.name })));
    console.log('初期選択メンバー:', this.initialSelectedMembers);
    
    // 初期選択状態を設定
    this.members.forEach(member => {
      this.selectedMembers[member.id] = this.initialSelectedMembers.includes(Number(member.id));
    });
    
    // グループの初期状態を設定
    this.updateGroupsByMember();
    
    // デバッグ情報：初期化後の状態
    console.log('初期化後のselectedMembers:', {...this.selectedMembers});
    console.log('初期化後のselectedGroups:', {...this.selectedGroups});
    console.log('=== デバッグ情報終了 ===');
  },
  methods: {
    // __ 関数の定義（Laravel Blade の __ 関数の代替）
    __(text) {
      return text; // 実際の翻訳機能が必要な場合は、ここを拡張
    },
    
    // 班グループが選択された時、所属メンバーを更新
    updateMembersByGroup(group) {
      const isChecked = this.selectedGroups[group.id];
      console.log('班グループが選択されました:', group.name, 'チェック状態:', isChecked);
      
      if (group.members && Array.isArray(group.members)) {
        console.log('グループのメンバー数:', group.members.length);
        
        // 新しいselectedMembersオブジェクトを作成して、リアクティビティを確保
        const newSelectedMembers = { ...this.selectedMembers };
        
        group.members.forEach(member => {
          // メンバーがオブジェクトの場合
          if (typeof member === 'object' && member !== null) {
            console.log('メンバー処理:', member.name, 'ID:', member.id);
            newSelectedMembers[member.id] = isChecked;
          } 
          // メンバーが数値や文字列の場合（IDとして扱う）
          else if (typeof member === 'number' || typeof member === 'string') {
            console.log('メンバーIDとして処理:', member);
            newSelectedMembers[member] = isChecked;
          } 
          // その他の形式
          else {
            console.log('メンバーが不正な形式です:', member);
          }
        });
        
        // 新しいオブジェクトを代入して、リアクティビティをトリガー
        this.selectedMembers = newSelectedMembers;
        
        // 更新後のselectedMembersの状態をログ出力
        console.log('更新後のselectedMembers:', {...this.selectedMembers});
      } else {
        console.log('グループにメンバーがないか、membersが配列ではありません');
      }
    },
    
    // メンバーのチェック状態を切り替える
    toggleMember(memberId, event) {
      console.log('メンバーのチェック状態を切り替えます:', memberId, 'チェック状態:', event.target.checked);
      
      // 新しいselectedMembersオブジェクトを作成して、リアクティビティを確保
      const newSelectedMembers = { ...this.selectedMembers };
      newSelectedMembers[memberId] = event.target.checked;
      
      // 新しいオブジェクトを代入して、リアクティビティをトリガー
      this.selectedMembers = newSelectedMembers;
      
      // 更新後のselectedMembersの状態をログ出力
      console.log('更新後のselectedMembers:', {...this.selectedMembers});
      
      // グループの状態を更新
      this.updateGroupsByMember();
    },
    
    // メンバーが選択された時、班グループの状態を更新
    updateGroupsByMember() {
      console.log('メンバー選択状態が変更されました');
      
      // 新しいselectedGroupsオブジェクトを作成して、リアクティビティを確保
      const newSelectedGroups = { ...this.selectedGroups };
      
      this.branchGroups.forEach(group => {
        if (group.members && Array.isArray(group.members)) {
          // すべてのメンバーがチェックされているか確認
          const allChecked = group.members.every(member => {
            // メンバーがオブジェクトの場合
            if (typeof member === 'object' && member !== null) {
              const isChecked = Boolean(this.selectedMembers[member.id]);
              console.log(`グループ ${group.name} のメンバー ${member.name} (ID: ${member.id}) のチェック状態: ${isChecked}`);
              return isChecked;
            }
            // メンバーが数値や文字列の場合（IDとして扱う）
            else if (typeof member === 'number' || typeof member === 'string') {
              const isChecked = Boolean(this.selectedMembers[member]);
              console.log(`グループ ${group.name} のメンバーID ${member} のチェック状態: ${isChecked}`);
              return isChecked;
            }
            return false;
          });
          
          console.log(`グループ ${group.name} の全メンバーチェック状態: ${allChecked}`);
          newSelectedGroups[group.id] = allChecked;
        }
      });
      
      // 新しいオブジェクトを代入して、リアクティビティをトリガー
      this.selectedGroups = newSelectedGroups;
      
      // 更新後のselectedGroupsの状態をログ出力
      console.log('更新後のselectedGroups:', {...this.selectedGroups});
    }
  }
}
</script>
