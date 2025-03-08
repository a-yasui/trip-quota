import { describe, it, expect, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import MemberSelector from '../MemberSelector.vue';

describe('MemberSelector', () => {
  // テスト用のモックデータ
  const branchGroups = [
    { id: 1, name: 'グループA', members: [{ id: 1 }, { id: 2 }] },
    { id: 2, name: 'グループB', members: [{ id: 3 }, { id: 4 }] }
  ];
  
  const members = [
    { id: 1, name: 'メンバー1' },
    { id: 2, name: 'メンバー2' },
    { id: 3, name: 'メンバー3' },
    { id: 4, name: 'メンバー4' }
  ];

  // 基本的なマウントオプション
  const createWrapper = (props = {}) => {
    return mount(MemberSelector, {
      props: {
        branchGroups,
        members,
        initialSelectedMembers: [],
        inputName: 'member_ids',
        ...props
      },
      global: {
        mocks: {
          __: (text) => text // 翻訳関数のモック
        }
      }
    });
  };

  it('コンポーネントが正しくレンダリングされる', () => {
    const wrapper = createWrapper();
    
    // 班グループが正しく表示されているか
    expect(wrapper.findAll('input[id^="group_"]').length).toBe(branchGroups.length);
    
    // メンバーが正しく表示されているか
    expect(wrapper.findAll('input[id^="member_"]').length).toBe(members.length);
    
    // 班グループの名前が正しく表示されているか
    branchGroups.forEach(group => {
      expect(wrapper.text()).toContain(`${group.name} (${group.members.length}名)`);
    });
    
    // メンバーの名前が正しく表示されているか
    members.forEach(member => {
      expect(wrapper.text()).toContain(member.name);
    });
  });

  it('初期選択状態が正しく設定される', () => {
    // メンバー1と3を初期選択
    const initialSelectedMembers = [1, 3];
    const wrapper = createWrapper({ initialSelectedMembers });
    
    // 選択されたメンバーのチェックボックスがチェックされているか
    initialSelectedMembers.forEach(id => {
      expect(wrapper.find(`input[id="member_${id}"]`).element.checked).toBe(true);
    });
    
    // 選択されていないメンバーのチェックボックスがチェックされていないか
    [2, 4].forEach(id => {
      expect(wrapper.find(`input[id="member_${id}"]`).element.checked).toBe(false);
    });
    
    // グループの選択状態も正しく設定されているか
    // グループAはメンバー1が選択されているが、メンバー2は選択されていないのでチェックされていない
    expect(wrapper.find('input[id="group_1"]').element.checked).toBe(false);
    // グループBはメンバー3が選択されているが、メンバー4は選択されていないのでチェックされていない
    expect(wrapper.find('input[id="group_2"]').element.checked).toBe(false);
  });

  it('班グループを選択すると所属メンバーが選択される', async () => {
    const wrapper = createWrapper();
    
    // グループAを選択
    await wrapper.find('input[id="group_1"]').setValue(true);
    
    // グループAに所属するメンバーが選択されているか
    expect(wrapper.find('input[id="member_1"]').element.checked).toBe(true);
    expect(wrapper.find('input[id="member_2"]').element.checked).toBe(true);
    
    // グループBに所属するメンバーは選択されていないか
    expect(wrapper.find('input[id="member_3"]').element.checked).toBe(false);
    expect(wrapper.find('input[id="member_4"]').element.checked).toBe(false);
    
    // グループBも選択
    await wrapper.find('input[id="group_2"]').setValue(true);
    
    // 全メンバーが選択されているか
    [1, 2, 3, 4].forEach(id => {
      expect(wrapper.find(`input[id="member_${id}"]`).element.checked).toBe(true);
    });
    
    // グループAの選択を解除
    await wrapper.find('input[id="group_1"]').setValue(false);
    
    // グループAに所属するメンバーの選択が解除されているか
    expect(wrapper.find('input[id="member_1"]').element.checked).toBe(false);
    expect(wrapper.find('input[id="member_2"]').element.checked).toBe(false);
    
    // グループBに所属するメンバーはまだ選択されているか
    expect(wrapper.find('input[id="member_3"]').element.checked).toBe(true);
    expect(wrapper.find('input[id="member_4"]').element.checked).toBe(true);
  });

  it('メンバーを個別に選択/解除するとグループの選択状態も更新される', async () => {
    const wrapper = createWrapper();
    
    // グループAに所属するメンバーを全て選択
    await wrapper.find('input[id="member_1"]').setValue(true);
    await wrapper.find('input[id="member_2"]').setValue(true);
    
    // グループAが選択されているか
    expect(wrapper.find('input[id="group_1"]').element.checked).toBe(true);
    
    // グループBは選択されていないか
    expect(wrapper.find('input[id="group_2"]').element.checked).toBe(false);
    
    // グループAに所属するメンバーの一人の選択を解除
    await wrapper.find('input[id="member_1"]').setValue(false);
    
    // グループAの選択が解除されているか
    expect(wrapper.find('input[id="group_1"]').element.checked).toBe(false);
    
    // グループBに所属するメンバーを全て選択
    await wrapper.find('input[id="member_3"]').setValue(true);
    await wrapper.find('input[id="member_4"]').setValue(true);
    
    // グループBが選択されているか
    expect(wrapper.find('input[id="group_2"]').element.checked).toBe(true);
  });

  it('updateMembersByGroupメソッドが正しく動作する', async () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm;
    
    // グループAを選択状態に設定
    vm.selectedGroups[1] = true;
    
    // updateMembersByGroupメソッドを呼び出し
    await vm.updateMembersByGroup(branchGroups[0]);
    
    // グループAに所属するメンバーが選択されているか
    expect(vm.selectedMembers[1]).toBe(true);
    expect(vm.selectedMembers[2]).toBe(true);
    
    // グループBに所属するメンバーは選択されていないか
    expect(vm.selectedMembers[3]).toBe(false);
    expect(vm.selectedMembers[4]).toBe(false);
    
    // グループAを非選択状態に設定
    vm.selectedGroups[1] = false;
    
    // updateMembersByGroupメソッドを呼び出し
    await vm.updateMembersByGroup(branchGroups[0]);
    
    // グループAに所属するメンバーの選択が解除されているか
    expect(vm.selectedMembers[1]).toBe(false);
    expect(vm.selectedMembers[2]).toBe(false);
  });

  it('updateGroupsByMemberメソッドが正しく動作する', async () => {
    const wrapper = createWrapper();
    const vm = wrapper.vm;
    
    // グループAに所属するメンバーを全て選択状態に設定
    vm.selectedMembers[1] = true;
    vm.selectedMembers[2] = true;
    
    // updateGroupsByMemberメソッドを呼び出し
    await vm.updateGroupsByMember();
    
    // グループAが選択されているか
    expect(vm.selectedGroups[1]).toBe(true);
    
    // グループBは選択されていないか
    expect(vm.selectedGroups[2]).toBe(false);
    
    // グループAに所属するメンバーの一人の選択を解除
    vm.selectedMembers[1] = false;
    
    // updateGroupsByMemberメソッドを呼び出し
    await vm.updateGroupsByMember();
    
    // グループAの選択が解除されているか
    expect(vm.selectedGroups[1]).toBe(false);
    
    // グループBに所属するメンバーを全て選択状態に設定
    vm.selectedMembers[3] = true;
    vm.selectedMembers[4] = true;
    
    // updateGroupsByMemberメソッドを呼び出し
    await vm.updateGroupsByMember();
    
    // グループBが選択されているか
    expect(vm.selectedGroups[2]).toBe(true);
  });

  it('inputNameプロパティが正しく反映される', () => {
    const customInputName = 'custom_member_ids';
    const wrapper = createWrapper({ inputName: customInputName });
    
    // メンバーのチェックボックスのname属性が正しく設定されているか
    wrapper.findAll('input[id^="member_"]').forEach(input => {
      expect(input.attributes('name')).toBe(customInputName + '[]');
    });
  });

  it('メンバーオブジェクトの形式が異なる場合も正しく動作する', async () => {
    // メンバーIDが直接配列に含まれるケース
    const altBranchGroups = [
      { id: 1, name: 'グループA', members: [1, 2] },
      { id: 2, name: 'グループB', members: [3, 4] }
    ];
    
    const wrapper = mount(MemberSelector, {
      props: {
        branchGroups: altBranchGroups,
        members,
        initialSelectedMembers: [],
        inputName: 'member_ids'
      },
      global: {
        mocks: {
          __: (text) => text
        }
      }
    });
    
    // グループAを選択
    await wrapper.find('input[id="group_1"]').setValue(true);
    
    // グループAに所属するメンバーが選択されているか
    expect(wrapper.find('input[id="member_1"]').element.checked).toBe(true);
    expect(wrapper.find('input[id="member_2"]').element.checked).toBe(true);
    
    // グループBに所属するメンバーは選択されていないか
    expect(wrapper.find('input[id="member_3"]').element.checked).toBe(false);
    expect(wrapper.find('input[id="member_4"]').element.checked).toBe(false);
  });
});
