import { describe, it, expect } from 'vitest';
import { getAvatarFallback } from '../avatar';

describe('getAvatarFallback', () => {
  describe('正常情況', () => {
    it('應該返回 full_name 的前兩個字元', () => {
      expect(getAvatarFallback({ full_name: '張三' })).toBe('張三');
      expect(getAvatarFallback({ full_name: 'John Doe' })).toBe('JO');
      expect(getAvatarFallback({ full_name: 'alice' })).toBe('AL');
    });

    it('應該處理單字元 full_name', () => {
      expect(getAvatarFallback({ full_name: '李' })).toBe('李');
      expect(getAvatarFallback({ full_name: 'A' })).toBe('A');
      expect(getAvatarFallback({ full_name: 'z' })).toBe('Z');
    });

    it('應該將英文字元轉換為大寫', () => {
      expect(getAvatarFallback({ full_name: 'john' })).toBe('JO');
      expect(getAvatarFallback({ full_name: 'alice' })).toBe('AL');
    });
  });

  describe('Fallback 到 name', () => {
    it('當 full_name 為 null 時，應該使用 name', () => {
      expect(getAvatarFallback({
        full_name: null,
        name: 'John'
      })).toBe('JO');
    });

    it('當 full_name 為 undefined 時，應該使用 name', () => {
      expect(getAvatarFallback({
        full_name: undefined,
        name: 'Alice'
      })).toBe('AL');
    });

    it('當 full_name 為空字串時，應該使用 name', () => {
      expect(getAvatarFallback({
        full_name: '',
        name: 'Bob'
      })).toBe('BO');
    });

    it('應該處理單字元 name', () => {
      expect(getAvatarFallback({
        full_name: null,
        name: 'J'
      })).toBe('J');
    });
  });

  describe('Fallback 到 username', () => {
    it('當 full_name 和 name 都為 null 時，應該使用 username', () => {
      expect(getAvatarFallback({
        full_name: null,
        name: null,
        username: 'john123'
      })).toBe('JO');
    });

    it('應該處理單字元 username', () => {
      expect(getAvatarFallback({
        full_name: null,
        name: null,
        username: 'j'
      })).toBe('J');
    });
  });

  describe('Fallback 到 email', () => {
    it('當所有名稱欄位都為 null 時，應該使用 email', () => {
      expect(getAvatarFallback({
        full_name: null,
        name: null,
        username: null,
        email: 'test@example.com'
      })).toBe('TE');
    });

    it('應該處理單字元 email（實際上會取前兩個字元）', () => {
      expect(getAvatarFallback({
        full_name: null,
        name: null,
        username: null,
        email: 't@example.com'
      })).toBe('T@');
    });

    it('應該正確處理不同格式的 email', () => {
      expect(getAvatarFallback({
        full_name: null,
        name: null,
        username: null,
        email: 'user.name@example.com'
      })).toBe('US');
    });
  });

  describe('最終 fallback', () => {
    it('當所有欄位都為 null 時，應該返回 "U"', () => {
      expect(getAvatarFallback({
        full_name: null,
        name: null,
        username: null,
        email: null
      })).toBe('U');
    });

    it('當所有欄位都為 undefined 時，應該返回 "U"', () => {
      expect(getAvatarFallback({
        full_name: undefined,
        name: undefined,
        username: undefined,
        email: undefined
      })).toBe('U');
    });

    it('當傳入空物件時，應該返回 "U"', () => {
      expect(getAvatarFallback({})).toBe('U');
    });

    it('當所有欄位都為空字串時，應該返回 "U"', () => {
      expect(getAvatarFallback({
        full_name: '',
        name: '',
        username: '',
        email: ''
      })).toBe('U');
    });

    it('當所有欄位都只有空格時，應該返回 "U"', () => {
      expect(getAvatarFallback({
        full_name: '   ',
        name: '  ',
        username: ' ',
        email: '    '
      })).toBe('U');
    });
  });

  describe('邊界情況', () => {
    it('應該處理空字串', () => {
      expect(getAvatarFallback({ full_name: '' })).toBe('U');
    });

    it('應該 trim 空格', () => {
      expect(getAvatarFallback({ full_name: '  John  ' })).toBe('JO');
      expect(getAvatarFallback({ full_name: ' A ' })).toBe('A');
    });

    it('應該處理只有空格的字串', () => {
      expect(getAvatarFallback({ full_name: '   ' })).toBe('U');
    });

    it('應該處理包含特殊字符的名稱', () => {
      expect(getAvatarFallback({ full_name: 'John-Doe' })).toBe('JO');
      expect(getAvatarFallback({ full_name: 'Mary.Jane' })).toBe('MA');
    });

    it('應該處理數字開頭的名稱', () => {
      expect(getAvatarFallback({ username: '123user' })).toBe('12');
    });

    it('應該處理混合中英文的名稱', () => {
      expect(getAvatarFallback({ full_name: '張Sam' })).toBe('張S');
      expect(getAvatarFallback({ full_name: 'John李' })).toBe('JO');
    });
  });

  describe('優先級測試', () => {
    it('應該優先使用 full_name，即使其他欄位也有值', () => {
      expect(getAvatarFallback({
        full_name: 'John',
        name: 'Jane',
        username: 'user',
        email: 'test@example.com'
      })).toBe('JO');
    });

    it('當 full_name 為 null 時，應該使用 name，即使 username 和 email 也有值', () => {
      expect(getAvatarFallback({
        full_name: null,
        name: 'Jane',
        username: 'user',
        email: 'test@example.com'
      })).toBe('JA');
    });

    it('當 full_name 和 name 都為 null 時，應該使用 username', () => {
      expect(getAvatarFallback({
        full_name: null,
        name: null,
        username: 'user',
        email: 'test@example.com'
      })).toBe('US');
    });
  });

  describe('性能測試', () => {
    it('應該在合理時間內完成（< 1ms）', () => {
      const start = performance.now();
      for (let i = 0; i < 1000; i++) {
        getAvatarFallback({ full_name: 'John Doe' });
      }
      const end = performance.now();
      const timePerCall = (end - start) / 1000;
      expect(timePerCall).toBeLessThan(1); // 每次呼叫應該 < 1ms
    });
  });
});
