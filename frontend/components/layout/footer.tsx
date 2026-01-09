import Link from 'next/link';
import { Heart } from 'lucide-react';

export function Footer() {
  const currentYear = new Date().getFullYear();

  const footerLinks = {
    features: {
      title: '功能',
      links: [
        { label: '搜尋業務員', href: '/search' },
        { label: '熱門業務員', href: '/search?sort=popular' },
        { label: '產業類別', href: '/industries' },
        { label: '服務地區', href: '/regions' },
      ],
    },
    about: {
      title: '關於',
      links: [
        { label: '關於 YAMU', href: '/about' },
        { label: '服務條款', href: '/terms' },
        { label: '隱私權政策', href: '/privacy' },
        { label: '常見問題', href: '/faq' },
      ],
    },
    account: {
      title: '帳號',
      links: [
        { label: '業務員註冊', href: '/register' },
        { label: '登入', href: '/login' },
        { label: '個人中心', href: '/dashboard' },
        { label: '聯絡我們', href: '/contact' },
      ],
    },
  };

  return (
    <footer className="border-t border-slate-200 bg-slate-50">
      <div className="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
        {/* Main Footer Content */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-8">
          {/* Brand Section */}
          <div className="lg:col-span-1">
            <Link href="/" className="flex items-center space-x-2 mb-4">
              <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-primary-500 to-secondary-500">
                <span className="text-xl font-bold text-white">Y</span>
              </div>
              <span className="text-2xl font-bold bg-gradient-to-r from-primary-600 to-secondary-600 bg-clip-text text-transparent">
                YAMU
              </span>
            </Link>
            <p className="text-sm text-slate-600 mb-4">
              專業業務員搜尋平台，連結優質業務員與需求客戶，打造透明、高效的商業合作環境。
            </p>
            <p className="text-xs text-slate-500">
              讓每一次合作都值得信賴
            </p>
          </div>

          {/* Links Sections */}
          {Object.entries(footerLinks).map(([key, section]) => (
            <div key={key}>
              <h3 className="text-sm font-semibold text-slate-900 uppercase tracking-wider mb-4">
                {section.title}
              </h3>
              <ul className="space-y-3">
                {section.links.map((link) => (
                  <li key={link.href}>
                    <Link
                      href={link.href}
                      className="text-sm text-slate-600 hover:text-primary-600 transition-colors"
                    >
                      {link.label}
                    </Link>
                  </li>
                ))}
              </ul>
            </div>
          ))}
        </div>

        {/* Bottom Bar */}
        <div className="pt-8 border-t border-slate-200">
          <div className="flex flex-col md:flex-row items-center justify-between space-y-4 md:space-y-0">
            {/* Copyright */}
            <div className="flex items-center text-sm text-slate-600">
              <span>&copy; {currentYear} YAMU. All rights reserved.</span>
            </div>

            {/* Made with Love */}
            <div className="flex items-center space-x-2 text-sm text-slate-600">
              <span>Made with</span>
              <Heart className="h-4 w-4 text-error-500 fill-error-500" />
              <span>by YAMU Team</span>
            </div>

            {/* Social Links / Legal Links */}
            <div className="flex items-center space-x-6 text-sm">
              <Link
                href="/terms"
                className="text-slate-600 hover:text-primary-600 transition-colors"
              >
                服務條款
              </Link>
              <Link
                href="/privacy"
                className="text-slate-600 hover:text-primary-600 transition-colors"
              >
                隱私權政策
              </Link>
              <Link
                href="/sitemap"
                className="text-slate-600 hover:text-primary-600 transition-colors"
              >
                網站地圖
              </Link>
            </div>
          </div>

          {/* Additional Info */}
          <div className="mt-4 text-center">
            <p className="text-xs text-slate-500">
              本網站為業務員推廣平台，所有刊登資訊均經過審核。若有任何問題請聯繫客服。
            </p>
          </div>
        </div>
      </div>
    </footer>
  );
}
