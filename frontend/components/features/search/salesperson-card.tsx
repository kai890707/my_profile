import Link from 'next/link';
import { Avatar } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { MapPin, Briefcase } from 'lucide-react';
import { SalespersonSearchResult } from '@/types/api';

interface SalespersonCardProps {
  salesperson: SalespersonSearchResult;
}

export function SalespersonCard({ salesperson }: SalespersonCardProps) {
  const specialtiesList = salesperson.specialties
    ? salesperson.specialties.split(',').slice(0, 3)
    : [];

  // Normalize service_regions to array
  const serviceRegions = (() => {
    const regions = salesperson.service_regions as string[] | string | null | undefined;
    if (!regions) return [];
    if (Array.isArray(regions)) return regions;
    if (typeof regions === 'string') {
      try {
        // Try parsing as JSON first
        const parsed = JSON.parse(regions);
        return Array.isArray(parsed) ? parsed : [regions];
      } catch {
        // If not JSON, split by comma
        return regions.split(',').map((r: string) => r.trim());
      }
    }
    return [];
  })();

  return (
    <Link href={`/salesperson/${salesperson.id}`}>
      <Card hover className="h-full transition-all duration-200 hover:shadow-xl">
        <CardContent className="p-6">
          {/* 頭像與基本資訊 */}
          <div className="flex items-start gap-4 mb-4">
            <Avatar
              src={salesperson.avatar}
              fallback={salesperson.full_name.substring(0, 2)}
              size="lg"
            />
            <div className="flex-1 min-w-0">
              <h3 className="text-lg font-semibold text-slate-900 truncate">
                {salesperson.full_name}
              </h3>
              {salesperson.company_name && (
                <div className="flex items-center gap-1 text-sm text-slate-600 mt-1">
                  <Briefcase className="h-4 w-4" />
                  <span className="truncate">{salesperson.company_name}</span>
                </div>
              )}
              {salesperson.industry_name && (
                <Badge variant="secondary" size="sm" className="mt-2">
                  {salesperson.industry_name}
                </Badge>
              )}
            </div>
          </div>

          {/* 簡介 */}
          {salesperson.bio && (
            <p className="text-sm text-slate-600 line-clamp-2 mb-4">
              {salesperson.bio}
            </p>
          )}

          {/* 專長標籤 */}
          {specialtiesList.length > 0 && (
            <div className="flex flex-wrap gap-2 mb-4">
              {specialtiesList.map((specialty, index) => (
                <Badge key={index} variant="primary" size="sm">
                  {specialty.trim()}
                </Badge>
              ))}
            </div>
          )}

          {/* 服務地區 */}
          {serviceRegions.length > 0 && (
            <div className="flex items-center gap-2 text-xs text-slate-500">
              <MapPin className="h-3.5 w-3.5" />
              <span>服務地區: {serviceRegions.join('、')}</span>
            </div>
          )}
        </CardContent>
      </Card>
    </Link>
  );
}
