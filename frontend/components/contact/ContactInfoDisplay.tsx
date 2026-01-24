'use client';

import { useCallback, useState } from 'react';
import { Phone, Mail, MessageSquare, User, Copy, CheckCircle2, Send, MessageCircle } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { toast } from 'sonner';
import type { ContactInfo } from '@/types/api';

interface ContactInfoDisplayProps {
  contactInfo: ContactInfo;
  onContactClick: () => void;
  salespersonName?: string;
}

interface ContactMethodItemProps {
  icon: React.ReactNode;
  label: string;
  value: string;
  href?: string;
  onClick?: () => void;
}

function ContactMethodItem({ icon, label, value, href, onClick }: ContactMethodItemProps) {
  const [copied, setCopied] = useState(false);

  const handleCopy = useCallback(async () => {
    try {
      await navigator.clipboard.writeText(value);
      setCopied(true);
      toast.success(`${label}已複製到剪貼簿`);
      setTimeout(() => setCopied(false), 2000);
    } catch (error) {
      toast.error('複製失敗，請手動複製');
    }
  }, [value, label]);

  return (
    <div className="flex items-center gap-3 p-4 bg-slate-50 rounded-xl border border-slate-200 hover:bg-slate-100 transition-colors group">
      <div className="flex items-center justify-center w-10 h-10 rounded-full bg-primary-100 text-primary-600">
        {icon}
      </div>
      <div className="flex-1 min-w-0">
        <p className="text-xs text-slate-500 mb-0.5">{label}</p>
        {href ? (
          <a
            href={href}
            onClick={onClick}
            className="text-sm font-medium text-slate-900 hover:text-primary-600 transition-colors break-all"
          >
            {value}
          </a>
        ) : (
          <p className="text-sm font-medium text-slate-900 break-all">{value}</p>
        )}
      </div>
      <button
        onClick={handleCopy}
        className="flex items-center justify-center w-8 h-8 rounded-lg hover:bg-white transition-colors opacity-0 group-hover:opacity-100"
        title="複製到剪貼簿"
      >
        {copied ? (
          <CheckCircle2 className="h-4 w-4 text-green-600" />
        ) : (
          <Copy className="h-4 w-4 text-slate-400" />
        )}
      </button>
    </div>
  );
}

export function ContactInfoDisplay({
  contactInfo,
  onContactClick,
  salespersonName = '業務員',
}: ContactInfoDisplayProps) {
  // Sort contact methods by preferences
  const sortedMethods = useCallback(() => {
    const methods: Array<{
      type: string;
      icon: React.ReactNode;
      label: string;
      value: string;
      href?: string;
    }> = [];

    const methodMap: Record<
      string,
      {
        value: string | null;
        icon: React.ReactNode;
        label: string;
        href?: string;
      }
    > = {
      phone: {
        value: contactInfo.phone,
        icon: <Phone className="h-5 w-5" />,
        label: '電話',
        href: contactInfo.phone ? `tel:${contactInfo.phone}` : undefined,
      },
      email_public: {
        value: contactInfo.email_public,
        icon: <Mail className="h-5 w-5" />,
        label: 'Email',
        href: contactInfo.email_public ? `mailto:${contactInfo.email_public}` : undefined,
      },
      line: {
        value: contactInfo.line_id,
        icon: <MessageSquare className="h-5 w-5" />,
        label: 'LINE',
        href: contactInfo.line_id ? `https://line.me/ti/p/${contactInfo.line_id}` : undefined,
      },
      wechat: {
        value: contactInfo.wechat_id,
        icon: <User className="h-5 w-5" />,
        label: 'WeChat',
        href: undefined,
      },
    };

    // Add methods according to preferences order
    if (contactInfo.contact_preferences && contactInfo.contact_preferences.length > 0) {
      contactInfo.contact_preferences.forEach((pref) => {
        const method = methodMap[pref];
        if (method && method.value) {
          methods.push({
            type: pref,
            icon: method.icon,
            label: method.label,
            value: method.value,
            href: method.href,
          });
        }
      });
    } else {
      // Default order if no preferences
      Object.entries(methodMap).forEach(([type, method]) => {
        if (method && method.value) {
          methods.push({
            type,
            icon: method.icon,
            label: method.label,
            value: method.value,
            href: method.href,
          });
        }
      });
    }

    return methods;
  }, [contactInfo]);

  const methods = sortedMethods();

  return (
    <Card>
      <CardHeader>
        <CardTitle>聯繫方式</CardTitle>
      </CardHeader>
      <CardContent>
        {contactInfo.has_contact_methods ? (
          <div className="space-y-4">
            {/* Contact Methods */}
            <div className="space-y-3">
              {methods.map((method, index) => (
                <ContactMethodItem
                  key={method.type}
                  icon={method.icon}
                  label={`${method.label}${
                    contactInfo.contact_preferences && index === 0 ? ' (首選)' : ''
                  }`}
                  value={method.value}
                  href={method.href}
                />
              ))}
            </div>

            {/* Preferred contact method note */}
            {contactInfo.contact_preferences && contactInfo.contact_preferences.length > 0 && (
              <p className="text-xs text-slate-500 px-4">
                業務員偏好的聯繫順序已按上方排列
              </p>
            )}

            {/* Message button */}
            <div className="pt-4 border-t border-slate-200">
              <Button onClick={onContactClick} className="w-full" size="lg">
                <Send className="mr-2 h-5 w-5" />
                立即聯繫 {salespersonName}
              </Button>
              <p className="text-xs text-slate-500 mt-2 text-center">
                或點擊上方聯繫方式直接撥打/發送訊息
              </p>
            </div>
          </div>
        ) : (
          <div className="text-center py-8">
            <MessageCircle className="mx-auto h-12 w-12 text-slate-300 mb-4" />
            <p className="text-base font-medium text-slate-600 mb-2">尚無聯繫方式</p>
            <p className="text-sm text-slate-500 mb-6">
              {salespersonName}尚未提供公開聯繫方式，但您仍可透過站內訊息聯繫。
            </p>
            <Button onClick={onContactClick} size="lg">
              <Send className="mr-2 h-5 w-5" />
              站內聯繫
            </Button>
          </div>
        )}
      </CardContent>
    </Card>
  );
}
