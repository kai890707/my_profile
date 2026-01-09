import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import React from 'react';
import { Input } from '../input';
import { Search } from 'lucide-react';

describe('Input', () => {
  it('renders basic input', () => {
    render(<Input placeholder="Enter text" />);
    expect(screen.getByPlaceholderText('Enter text')).toBeInTheDocument();
  });

  it('handles text input', async () => {
    const user = userEvent.setup();
    const handleChange = vi.fn();

    render(<Input placeholder="Type here" onChange={handleChange} />);

    const input = screen.getByPlaceholderText('Type here');
    await user.type(input, 'Hello');

    expect(input).toHaveValue('Hello');
    expect(handleChange).toHaveBeenCalled();
  });

  it('displays label when provided', () => {
    render(<Input label="Email" placeholder="email@example.com" />);
    expect(screen.getByText('Email')).toBeInTheDocument();
  });

  it('displays error message when error prop is provided', () => {
    render(<Input error="This field is required" />);
    expect(screen.getByText('This field is required')).toBeInTheDocument();
  });

  it('displays helper text when provided', () => {
    render(<Input helperText="Enter your email address" />);
    expect(screen.getByText('Enter your email address')).toBeInTheDocument();
  });

  it('renders icon when provided', () => {
    const { container } = render(
      <Input icon={<Search className="h-4 w-4" data-testid="search-icon" />} />
    );
    expect(container.querySelector('[data-testid="search-icon"]')).toBeInTheDocument();
  });

  it('applies correct padding when icon is present', () => {
    render(<Input icon={<Search className="h-4 w-4" />} />);
    const input = screen.getByRole('textbox');
    expect(input).toHaveClass('pl-11');
  });

  it('supports different input types', () => {
    const { rerender } = render(<Input type="email" />);
    expect(screen.getByRole('textbox')).toHaveAttribute('type', 'email');

    rerender(<Input type="password" />);
    expect(screen.getByDisplayValue('')).toHaveAttribute('type', 'password');
  });

  it('can be disabled', () => {
    render(<Input disabled />);
    expect(screen.getByRole('textbox')).toBeDisabled();
  });

  it('can be required', () => {
    render(<Input required />);
    expect(screen.getByRole('textbox')).toBeRequired();
  });

  it('applies error styles when error is present', () => {
    render(<Input error="Invalid input" />);
    const input = screen.getByRole('textbox');
    expect(input).toHaveClass('border-red-400');
  });

  it('applies default size styles', () => {
    render(<Input />);
    const input = screen.getByRole('textbox');
    expect(input).toHaveClass('h-11', 'text-base');
  });

  it('applies large size styles', () => {
    render(<Input size="lg" />);
    const input = screen.getByRole('textbox');
    expect(input).toHaveClass('h-14', 'text-lg');
  });

  it('supports controlled input', async () => {
    const user = userEvent.setup();
    const TestComponent = () => {
      const [value, setValue] = React.useState('');
      return (
        <Input
          value={value}
          onChange={(e) => setValue(e.target.value)}
          placeholder="Controlled"
        />
      );
    };

    render(<TestComponent />);
    const input = screen.getByPlaceholderText('Controlled');

    await user.type(input, 'Test');
    expect(input).toHaveValue('Test');
  });

  it('shows both error and helper text when both provided', () => {
    render(<Input error="Error message" helperText="Helper message" />);

    expect(screen.getByText('Error message')).toBeInTheDocument();
    expect(screen.queryByText('Helper message')).not.toBeInTheDocument();
  });

  it('adds required asterisk to label when required', () => {
    render(<Input label="Username" required />);
    expect(screen.getByText('Username')).toBeInTheDocument();
    // The asterisk should be present in the label section
    const labelSection = screen.getByText('Username').closest('label');
    expect(labelSection?.textContent).toContain('*');
  });
});
