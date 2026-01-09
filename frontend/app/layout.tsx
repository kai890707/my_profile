import { Inter, Noto_Sans_TC } from 'next/font/google';
import './globals.css';
import { Providers } from './providers';

const inter = Inter({
  subsets: ['latin'],
  variable: '--font-inter',
  display: 'swap',
});

const notoSansTC = Noto_Sans_TC({
  weight: ['400', '500', '700'],
  variable: '--font-noto-sans-tc',
  display: 'swap',
  preload: false,
});

export const metadata = {
  title: 'YAMU - 業務員搜尋平台',
  description: '尋找最適合您的專業業務員',
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="zh-TW" className={`${inter.variable} ${notoSansTC.variable}`}>
      <body className="font-sans antialiased bg-background text-foreground">
        <Providers>{children}</Providers>
      </body>
    </html>
  );
}
