document.addEventListener('DOMContentLoaded', function() {
    const apiRoot = ammData.apiRoot;
    const nonce = ammData.nonce;

    // Tab Logic
    document.querySelectorAll('.amm-nav-item').forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            document.querySelectorAll('.amm-nav-item').forEach(i => i.classList.remove('active'));
            document.querySelectorAll('.amm-tab-content').forEach(c => c.classList.remove('active'));
            item.classList.add('active');
            document.getElementById('tab-' + item.dataset.tab).classList.add('active');
        });
    });

    // Onboarding Logic
    window.closeOnboarding = function() {
        document.getElementById('amm-onboarding-overlay').style.display = 'none';
        localStorage.setItem('amm_onboarded', 'yes');
    };
    if (!localStorage.getItem('amm_onboarded')) {
        document.getElementById('amm-onboarding-overlay').style.display = 'flex';
    }

    // Theme Logic
    document.getElementById('amm-theme-toggle').addEventListener('click', () => {
        document.getElementById('amm-dashboard-root').classList.toggle('dark-mode');
    });

    // Template Logic
    document.getElementById('amm-template-select').addEventListener('change', (e) => {
        if(e.target.value) document.getElementById('amm-input').value = e.target.value;
    });

    // Fetch Data
    async function safeFetch(endpoint, options = {}) {
        const defaultOptions = {
            headers: { 'X-WP-Nonce': nonce }
        };
        const mergedOptions = { ...defaultOptions, ...options };
        if (options.body) {
            mergedOptions.headers['Content-Type'] = 'application/json';
        }

        try {
            const res = await fetch(apiRoot + endpoint, mergedOptions);
            if (!res.ok) {
                const errorData = await res.json();
                throw new Error(errorData.message || 'Network error');
            }
            return await res.json();
        } catch (err) {
            console.error('AMM Fetch Error:', err.message);
            alert('Engine Error: ' + err.message);
            throw err;
        }
    }

    function initApp() {
        // User Stats
        safeFetch('/user')
            .then(data => {
                const used = data.usage.used;
                const limit = data.usage.limit;
                const pct = Math.min(100, (used / limit) * 100);

                document.getElementById('amm-user-profile').innerText = `Welcome, ${data.user_name}${data.settings.company_name ? ' @ ' + data.settings.company_name : ''}`;

                // Apply Branding
                if (data.team_branding) {
                    if (data.team_branding.logo) {
                        document.querySelector('.amm-logo').innerHTML = `<img src="${data.team_branding.logo}" style="max-width:100%; max-height:40px;">`;
                    }
                    if (data.team_branding.color) {
                        document.documentElement.style.setProperty('--amm-primary', data.team_branding.color);
                    }
                }

                window.ammUserPlan = data.plan;
                document.getElementById('amm-user-stats-sidebar').innerHTML = `<strong>${data.plan.toUpperCase()}</strong><br>${used}/${limit} credits`;
                document.getElementById('amm-usage-bar').style.width = pct + '%';

                if (data.plan === 'pro' || data.plan === 'agency') {
                    const builder = document.getElementById('amm-builder-container');
                    if(builder) builder.style.display = 'block';
                }
                if (data.plan === 'agency') {
                    const branding = document.getElementById('amm-branding-container');
                    if(branding) branding.style.display = 'block';
                }
                if (data.plan !== 'free') {
                    const portal = document.getElementById('amm-portal-container');
                    if(portal) portal.style.display = 'block';
                }

                // Populate Settings
                document.getElementById('amm-set-webhook').value = data.settings.webhook_url || '';
                document.getElementById('amm-set-company').value = data.settings.company_name || '';
                document.getElementById('amm-set-default-mind').value = data.settings.default_mind || 'ceo';
                document.getElementById('amm-set-kb').value = data.settings.knowledge_base || '';
                document.getElementById('amm-set-alerts').checked = data.settings.usage_alerts;

                // Recent Activity
                const recent = document.getElementById('amm-recent-activity');
                safeFetch('/outputs')
                    .then(outputs => {
                        recent.innerHTML = outputs.slice(0, 5).map(o => `<div style="padding:10px; border-bottom:1px solid #eee;"><strong>${o.title}</strong> - ${o.date}</div>`).join('') || 'No recent activity.';
                    });

                // Populate Insights
                if (document.getElementById('amm-insight-gens')) {
                    const gens = data.insights.total_generations;
                    document.getElementById('amm-insight-gens').innerText = gens;
                    document.getElementById('amm-insight-gens-bar').style.width = Math.min(100, (gens / 50) * 100) + '%';
                    document.getElementById('amm-insight-refs').innerText = data.insights.referral_count;

							const trends = data.usage.trends;
							const max = Math.max(...trends, 1);
							document.getElementById('amm-usage-trends').innerHTML = trends.map(t => `<div style="flex:1; background:#007cba; height:${(t/max)*100}%; border-radius:3px;" title="${t} generations"></div>`).join('');
                }
            });

        // Templates
        safeFetch('/templates')
            .then(templates => {
                if(templates.length) {
                    const select = document.getElementById('amm-template-select');
                    select.innerHTML += templates.map(t => `<option value="${t.content}" ${t.locked ? 'disabled' : ''}>${t.title}${t.locked ? ' (Locked)' : ''}</option>`).join('');

							document.getElementById('amm-templates-grid').innerHTML = templates.map(t => `
								<div class="amm-plan-card ${t.premium ? 'premium' : ''}">
									<div style="font-size:32px; margin-bottom:10px;">📜</div>
									<strong>${t.title}</strong>
									<p style="font-size:10px; color:#888;">${t.premium ? 'Premium Marketplace Template' : 'Core Template'}</p>
									<button class="amm-secondary-btn" onclick="document.getElementById('amm-template-select').value='${t.content}'; document.getElementById('amm-input').value='${t.content}'; document.querySelector('[data-tab=generate]').click();" ${t.locked ? 'disabled' : ''}>Use Template</button>
									${t.locked && t.premium && !t.purchased ? `<button class="amm-primary-btn" style="margin-top:10px; font-size:10px;" onclick="ammCheckout('template_${t.id}')">Unlock for $${t.price}</button>` : ''}
									${t.locked && !t.premium ? `<button class="amm-primary-btn" style="margin-top:10px; font-size:10px;" onclick="document.querySelector('[data-tab=billing]').click()">Upgrade Plan to Unlock</button>` : ''}
								</div>
							`).join('');
                }
            });

        // Minds
        safeFetch('/minds')
            .then(minds => {
                window.ammAllMinds = minds;
                const options = minds.map(m => `<option value="${m.id}">${m.name}</option>`).join('');
                document.getElementById('amm-mind-select').innerHTML = options;
                document.getElementById('amm-set-default-mind').innerHTML = options;
                document.querySelectorAll('.amm-council-select').forEach(s => s.innerHTML += options);

                const categories = [...new Set(minds.map(m => m.category).filter(Boolean))];
                document.getElementById('amm-category-filter').innerHTML += categories.map(c => `<option value="${c}">${c}</option>`).join('');

                renderMindGrid(minds);
            });

        function renderMindGrid(minds) {
            document.getElementById('amm-marketplace-grid').innerHTML = minds.sort((a,b) => (b.featured||0) - (a.featured||0)).map(m => `
                <div class="amm-plan-card ${m.premium ? 'premium' : ''} ${m.featured ? 'featured' : ''}">
                    <div style="font-size:32px; margin-bottom:10px;">🧠</div>
                    <strong>${m.name}</strong>
                    <p style="font-size:10px; color:#888;">${m.category || 'Core'}</p>
                    <p>${m.premium ? 'Premium Mind' : 'Core Mind'}</p>
                    <button class="amm-secondary-btn" onclick="document.getElementById('amm-mind-select').value='${m.id}'; document.querySelector('[data-tab=generate]').click();" ${m.premium && !m.purchased ? 'disabled' : ''}>Use Mind</button>
                    ${m.premium && !m.purchased ? `<button class="amm-primary-btn" style="margin-top:10px; font-size:10px;" onclick="ammCheckout('mind_${m.id}')">Unlock for $49</button>` : ''}
                </div>
            `).join('');
        }

        document.getElementById('amm-category-filter').addEventListener('change', () => applyFilters());
        document.getElementById('amm-mind-filter').addEventListener('change', () => applyFilters());

        function applyFilters() {
            const cat = document.getElementById('amm-category-filter').value;
            const type = document.getElementById('amm-mind-filter').value;

            let filtered = window.ammAllMinds;

            if (cat) filtered = filtered.filter(m => m.category === cat);

            if (type === 'purchased') {
                filtered = filtered.filter(m => !m.premium || m.purchased);
            } else if (type === 'premium') {
                filtered = filtered.filter(m => m.premium && !m.purchased);
            }

            renderMindGrid(filtered);
        }

        // Folders
        safeFetch('/folders')
            .then(folders => {
                document.getElementById('amm-workspace-folder-filter').innerHTML += folders.map(f => `<option value="${f.name}">${f.name}</option>`).join('');
            });

        // Workspace
        window.refreshWorkspace = function() {
            safeFetch('/outputs')
                .then(outputs => {
                    const workspaceList = document.getElementById('amm-workspace-list');
                    if (!outputs.length) {
                        workspaceList.innerHTML = 'No outputs saved.';
                        return;
                    }
                    workspaceList.innerHTML = '<table style="width:100%; text-align:left;">' +
                        '<tr><th><input type="checkbox" id="amm-select-all"></th><th>Date</th><th>Title</th><th>Folders</th><th>Actions</th></tr>' +
                        outputs.map(o => `<tr data-folders="${o.folders.join(',')}" data-public="${o.is_public ? 'yes' : 'no'}"><td><input type="checkbox" class="amm-out-check" value="${o.id}"></td><td>${o.date}</td><td>${o.title} ${o.is_public ? '<span style="color:green; font-size:10px;">(SHARED)</span>' : ''}</td><td>${o.folders.join(', ') || '-'}</td><td><button class="amm-secondary-btn" onclick='ammEdit(${JSON.stringify(o)})'>✏️ Edit</button> <button class="amm-secondary-btn" onclick="ammDuplicate(${o.id})">👯 Duplicate</button> <button class="amm-secondary-btn" onclick="ammShare(${o.id})">🔗 ${o.is_public ? 'Unshare' : 'Share'}</button> <button class="amm-secondary-btn" onclick="ammFeedback(${o.id}, 'up')">👍</button><button class="amm-secondary-btn" onclick="ammFeedback(${o.id}, 'down')">👎</button></td></tr>`).join('') +
                        '</table>';

                    document.getElementById('amm-select-all').addEventListener('change', (e) => {
                        document.querySelectorAll('.amm-out-check').forEach(c => c.checked = e.target.checked);
                    });
                });
        }
        refreshWorkspace();

        // Team & Invites
        safeFetch('/teams')
            .then(teams => {
                const createBtn = document.getElementById('amm-create-team-btn');
                const controls = document.getElementById('amm-team-controls');

                if(teams.length) {
                    if(controls) controls.style.display = 'block';
                    window.ammActiveTeamId = teams[0].id;
                    document.getElementById('amm-team-list').innerHTML = teams.map(t => `
                        <div style="margin-bottom:10px; border-bottom:1px solid #eee; padding-bottom:10px;">
                            <strong>${t.team_name}</strong> (${t.role})
                            ${t.role === 'admin' ? '' : `
                                <button class="amm-secondary-btn" onclick="ammRemoveMember(${t.user_id}, ${t.id})" style="background:#ff4444; color:#fff;">Remove</button>
                                <div style="font-size:10px; margin-top:5px;">
                                    <label><input type="checkbox" onchange="ammTogglePerm(${t.user_id}, ${t.id}, 'can_generate', this.checked)" checked> Can Ignite</label>
                                    <label><input type="checkbox" onchange="ammTogglePerm(${t.user_id}, ${t.id}, 'can_view_workspace', this.checked)" checked> View Shared</label>
                                </div>
                            `}
                        </div>
                    `).join('');

                    // Fetch Invites
                    safeFetch('/pending-invites?team_id=' + teams[0].id)
                        .then(invites => {
                            if(invites.length) {
                                document.getElementById('amm-invite-list').innerHTML = invites.map(i => `<div>${i.email} <button class="amm-secondary-btn" onclick="ammRevokeInvite(${i.id})" style="background:#ff4444; color:#fff;">Revoke</button></div>`).join('');
                            }
                        });

                    // Fetch Team Activity
                    safeFetch('/team-activity?team_id=' + teams[0].id)
                        .then(activity => {
                            if(activity.length) {
                                document.getElementById('amm-team-activity').innerHTML = activity.map(a => `<div><strong>${a.user}</strong> generated <em>${a.title}</em></div>`).join('');
                            } else {
                                document.getElementById('amm-team-activity').innerHTML = 'No recent team activity.';
                            }
                        });
                } else if(window.ammUserPlan === 'agency') {
                    if(createBtn) createBtn.style.display = 'block';
                }
            });

        // Plans
        safeFetch('/plans')
            .then(plans => {
                document.getElementById('amm-plans-grid').innerHTML = plans.map(p => `
                    <div class="amm-plan-card ${p.featured ? 'featured' : ''}">
                        <h4>${p.name}</h4>
                        <p>${p.price}/mo</p>
                        <p style="font-size:12px; color:#888;">${p.credits} Credits / mo</p>
                        <button onclick="ammCheckout('${p.id}')" class="amm-primary-btn">Select</button>
                    </div>
                `).join('');
            });

        // Billing History
        safeFetch('/invoices')
            .then(history => {
                const historyBox = document.getElementById('amm-billing-history');
                if (history.length === 0) {
                    historyBox.innerHTML = 'No payment history found.';
                    return;
                }
                historyBox.innerHTML = '<table style="width:100%">' +
                    '<tr><th>Date</th><th>Plan</th><th>Gateway</th><th>Status</th></tr>' +
                    history.map(h => `<tr><td>${h.created_at}</td><td>${h.plan_id.toUpperCase()}</td><td>${h.gateway}</td><td>${h.status}</td></tr>`).join('') +
                    '</table>';
            });

        // Affiliate
        safeFetch('/affiliate')
            .then(data => {
                let html = `<p>Referral Link: <input type="text" value="${data.link}" readonly style="width:100%;"></p>
                    <p>Earnings: $${data.commissions}</p>
                    <h4>Your Referrals</h4>`;

                if(data.referrals && data.referrals.length) {
                    html += '<table style="width:100%">' + data.referrals.map(r => `<tr><td>${r.user_email}</td><td>${r.status}</td><td>$${r.commission_amount}</td></tr>`).join('') + '</table>';
                } else {
                    html += '<p>No referrals yet. Share your link to start earning!</p>';
                }
                document.getElementById('amm-affiliate-info').innerHTML = html;
            });
    }
    initApp();
    loadPresets();
    loadPersona();

    function loadPersona() {
        safeFetch('/persona')
            .then(p => {
                if (p.name) {
                    document.getElementById('amm-persona-name').value = p.name;
                    document.getElementById('amm-persona-pain').value = p.pain;
                    document.getElementById('amm-persona-desire').value = p.desire;
                    document.getElementById('amm-persona-triggers').value = p.triggers;
                }
            });
    }

    document.getElementById('amm-save-persona-btn').addEventListener('click', () => {
        const persona = {
            name: document.getElementById('amm-persona-name').value,
            pain: document.getElementById('amm-persona-pain').value,
            desire: document.getElementById('amm-persona-desire').value,
            triggers: document.getElementById('amm-persona-triggers').value
        };
        safeFetch('/save-persona', {
            method: 'POST',
            body: JSON.stringify(persona)
        }).then(data => { if(data.success) alert('Audience Context Saved!'); });
    });

    function loadPresets() {
        safeFetch('/presets')
            .then(presets => {
                const list = document.getElementById('amm-presets-list');
                const container = document.getElementById('amm-presets-container');
                if (presets.length) {
                    container.style.display = 'block';
                    list.innerHTML = presets.map(p => `<button class="amm-secondary-btn" onclick='applyPreset(${JSON.stringify(p)})'>${p.name}</button>`).join('');
                }
            });
    }

    window.applyPreset = function(p) {
        const selectors = document.querySelectorAll('.amm-council-select');
        p.mind_ids.forEach((id, i) => { if(selectors[i]) selectors[i].value = id; });
        document.querySelector(`input[name="council-mode"][value="${p.mode}"]`).checked = true;
    };

    // Handle Save Preset
    document.getElementById('amm-save-preset-btn').addEventListener('click', () => {
        const name = prompt('Preset Name (e.g. Weekly Audit):');
        if(!name) return;
        const mind_ids = Array.from(document.querySelectorAll('.amm-council-select')).map(s => s.value).filter(Boolean);
        const mode = document.querySelector('input[name="council-mode"]:checked').value;

        safeFetch('/save-preset', {
            method: 'POST',
            body: JSON.stringify({ name, mind_ids, mode })
        }).then(data => { if(data.success) { alert('Preset Saved!'); loadPresets(); } });
    });

    // Basic HTML Sanitizer for AI content
    function sanitizeHTML(str) {
        const div = document.createElement('div');
        div.textContent = str;
        // Basic line-break and list-item preservation
        return div.innerHTML.replace(/\n/g, '<br>');
    }

    // Generate Logic (Typewriter Effect)
    const btn = document.getElementById('amm-generate-btn');
    const outputBox = document.getElementById('amm-output');
    window.ammChatHistory = [];

    // Load History
    safeFetch('/get-history')
        .then(h => { window.ammChatHistory = h; });

    btn.addEventListener('click', () => {
        const userInput = document.getElementById('amm-input').value;
        if(!userInput) return;

        btn.disabled = true;
        outputBox.innerText = "The mind is thinking...";

        safeFetch('/generate', {
            method: 'POST',
            body: JSON.stringify({
                mind_id: document.getElementById('amm-mind-select').value,
                output_type: document.getElementById('amm-type-select').value,
                user_input: userInput,
                language: document.getElementById('amm-language-select').value,
                history: window.ammChatHistory
            })
        })
        .then(data => {
            if(data.success) {
                outputBox.innerText = "";
                let i = 0;
                const text = data.content;
                const interval = setInterval(() => {
                    outputBox.innerText += text[i];
                    i++;
                    if(i >= text.length) {
                        clearInterval(interval);
                        btn.disabled = false;
                        document.getElementById('amm-export-btn').style.display = 'block';
                        document.getElementById('amm-export-html-btn').style.display = 'block';
                        window.ammChatHistory.push({ role: 'user', content: userInput });
                        window.ammChatHistory.push({ role: 'assistant', content: text });

								// Save History
								safeFetch('/save-history', {
									method: 'POST',
									body: JSON.stringify({ history: window.ammChatHistory })
								});

								const successMsg = document.createElement('div');
								successMsg.style = 'margin-top:20px; font-size:12px; color:green; font-weight:bold;';
								successMsg.textContent = '✅ Strategy saved and persistent memory updated.';
								outputBox.appendChild(successMsg);
                    }
                }, 5);
            } else {
                outputBox.innerText = "Error: " + data.message;
                btn.disabled = false;
            }
        });
    });

    // Feedback Logic
    window.ammFeedback = function(id, rating) {
        safeFetch('/feedback', {
            method: 'POST',
            body: JSON.stringify({ post_id: id, rating: rating })
        }).then(data => { if(data.success) alert('Feedback recorded. Thank you!'); });
    };

    // Share Logic
    window.ammShare = function(id) {
        safeFetch('/share', {
            method: 'POST',
            body: JSON.stringify({ post_id: id })
        })
        .then(data => {
            if(data.is_public) {
                prompt('Public link copied to clipboard (Ctrl+C):', data.share_url);
            } else {
                alert('Intelligence marked as private.');
            }
            refreshWorkspace();
        });
    };

    // Handle Workspace Filters
    const wsFolderFilter = document.getElementById('amm-workspace-folder-filter');
    if(wsFolderFilter) wsFolderFilter.addEventListener('change', () => filterWorkspace());
    const wsTypeFilter = document.getElementById('amm-workspace-filter');
    if(wsTypeFilter) wsTypeFilter.addEventListener('change', () => filterWorkspace());

    function filterWorkspace() {
        const folder = document.getElementById('amm-workspace-folder-filter').value;
        const type = document.getElementById('amm-workspace-filter').value;

        document.querySelectorAll('#amm-workspace-list tr').forEach(tr => {
            if (tr.querySelector('th')) return;

            const folders = tr.dataset.folders || '';
            const isPublic = tr.dataset.public === 'yes';

            let show = true;
            if (folder && !folders.includes(folder)) show = false;
            if (type === 'public' && !isPublic) show = false;

            tr.style.display = show ? '' : 'none';
        });
    }

    // Handle Search
    document.getElementById('amm-workspace-search').addEventListener('input', (e) => {
        const term = e.target.value.toLowerCase();
        document.querySelectorAll('#amm-workspace-list tr').forEach(tr => {
            if (tr.querySelector('th')) return;
            const title = tr.innerText.toLowerCase();
            tr.style.display = title.includes(term) ? '' : 'none';
        });
    });

    // Edit Logic
    window.ammEdit = function(o) {
        window.ammActiveEditId = o.id;
        document.getElementById('amm-edit-content').value = o.content;
        document.getElementById('amm-edit-modal').style.display = 'flex';
    };

    document.getElementById('amm-save-edit-btn').addEventListener('click', () => {
        const content = document.getElementById('amm-edit-content').value;
        safeFetch('/update-output', {
            method: 'POST',
            body: JSON.stringify({ post_id: window.ammActiveEditId, content })
        }).then(data => {
            if(data.success) {
                document.getElementById('amm-edit-modal').style.display = 'none';
                refreshWorkspace();
            }
        });
    });

    // Duplicate Logic
    window.ammDuplicate = function(id) {
        safeFetch('/duplicate', {
            method: 'POST',
            body: JSON.stringify({ post_id: id })
        }).then(data => { if(data.success) refreshWorkspace(); });
    };

    // Portal Logic
    window.ammPortal = function() {
        safeFetch('/billing-portal')
            .then(data => { if(data.url) window.location.href = data.url; });
    };

    // Quick Ignite
    window.quickIgnite = function(mindId) {
        document.getElementById('amm-mind-select').value = mindId;
        document.querySelector('[data-tab=generate]').click();
    };

    // Export PDF
    document.getElementById('amm-export-btn').addEventListener('click', () => {
        const win = window.open('', '_blank');
        win.document.write(`<html><body style="font-family:sans-serif; padding:50px;"><h1>AI Multi-Mind Output</h1><hr>${outputBox.innerText}</body></html>`);
        win.print();
    });

    // Export HTML
    document.getElementById('amm-export-html-btn').addEventListener('click', () => {
        const blob = new Blob([outputBox.innerText], { type: 'text/html' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'ai-strategy.html';
        a.click();
    });

    // Handle Bulk Delete
    document.getElementById('amm-bulk-delete-btn').addEventListener('click', () => {
        const ids = Array.from(document.querySelectorAll('.amm-out-check:checked')).map(c => c.value);
        if(ids.length === 0 || !confirm('Delete selected?')) return;

        safeFetch('/bulk-action', {
            method: 'POST',
            body: JSON.stringify({ ids, action: 'delete' })
        }).then(data => { if(data.success) refreshWorkspace(); });
    });

    // Handle New Folder
    document.getElementById('amm-new-folder-btn').addEventListener('click', () => {
        const name = prompt('Enter folder name:');
        if(!name) return;

        safeFetch('/create-folder', {
            method: 'POST',
            body: JSON.stringify({ name })
        }).then(data => {
            if(data.success) {
                alert('Folder Created!');
                refreshWorkspace();
            }
        });
    });

    // Handle Refine Prompt
    document.getElementById('amm-refine-btn').addEventListener('click', () => {
        const input = document.getElementById('amm-input');
        if(!input.value) return;

        document.getElementById('amm-refine-btn').innerText = 'Magic in progress...';
        safeFetch('/refine-prompt', {
            method: 'POST',
            body: JSON.stringify({ user_input: input.value })
        })
        .then(data => {
            if(data.refined_prompt) {
                input.value = data.refined_prompt;
            }
        })
        .finally(() => {
            document.getElementById('amm-refine-btn').innerText = '✨ Refine with Magic BFF';
        });
    });

    // Handle Create Mind
    if (document.getElementById('amm-create-mind-btn')) {
        document.getElementById('amm-create-mind-btn').addEventListener('click', () => {
            safeFetch('/create-mind', {
                method: 'POST',
                body: JSON.stringify({
                    name: document.getElementById('amm-new-mind-name').value,
                    role: document.getElementById('amm-new-mind-role').value,
                    framework: document.getElementById('amm-new-mind-framework').value,
                    style: document.getElementById('amm-new-mind-style').value,
                    structure: document.getElementById('amm-new-mind-structure').value,
                    prompt: document.getElementById('amm-new-mind-prompt').value
                })
            }).then(data => { if(data.success) { alert('Custom Mind Created!'); location.reload(); } });
        });
    }

    // Handle Create Template
    if (document.getElementById('amm-create-template-btn')) {
        document.getElementById('amm-create-template-btn').addEventListener('click', () => {
            safeFetch('/create-template', {
                method: 'POST',
                body: JSON.stringify({
                    title: document.getElementById('amm-new-template-title').value,
                    content: document.getElementById('amm-new-template-content').value
                })
            }).then(data => { if(data.success) { alert('Template Created!'); location.reload(); } });
        });
    }

    // Handle Branding
    document.getElementById('amm-branding-btn').addEventListener('click', () => {
        safeFetch('/update-branding', {
            method: 'POST',
            body: JSON.stringify({
                team_id: window.ammActiveTeamId,
                logo: document.getElementById('amm-branding-logo').value,
                color: document.getElementById('amm-branding-color').value
            })
        }).then(data => { if(data.success) alert('Branding Updated!'); });
    });

    // Handle Remove Member
    window.ammRemoveMember = function(userId, teamId) {
        if(!confirm('Remove this member?')) return;
        safeFetch('/remove-member', {
            method: 'POST',
            body: JSON.stringify({ user_id: userId, team_id: teamId })
        }).then(data => { if(data.success) location.reload(); });
    };

    // Handle Revoke Invite
    window.ammRevokeInvite = function(id) {
        if(!confirm('Revoke this invite?')) return;
        safeFetch('/revoke-invite', {
            method: 'POST',
            body: JSON.stringify({ id })
        }).then(data => { if(data.success) location.reload(); });
    };

    // Handle Invite
    document.getElementById('amm-invite-btn').addEventListener('click', () => {
        const email = document.getElementById('amm-invite-email').value;
        if(!email) return;

        safeFetch('/invite', {
            method: 'POST',
            body: JSON.stringify({ team_id: window.ammActiveTeamId, email })
        }).then(data => {
            if(data.success) {
                prompt('Invite link generated! Send this to your team member:', data.invite_url);
            }
        });
    });

    // Handle Council
    document.getElementById('amm-ignite-council-btn').addEventListener('click', () => {
        const mind_ids = Array.from(document.querySelectorAll('.amm-council-select')).map(s => s.value).filter(Boolean);
        const user_input = document.getElementById('amm-council-input').value;
        const mode = document.querySelector('input[name="council-mode"]:checked').value;
        const output = document.getElementById('amm-council-output');

        output.innerText = 'The Council is deliberating...';
        safeFetch('/collaborate', {
            method: 'POST',
            body: JSON.stringify({ mind_ids, user_input, mode })
        }).then(data => {
            if(data.success) {
                output.innerHTML = ''; // Clear and build safely
                const title = document.createElement('h3');
                title.textContent = `Council Deliberation Complete (${mode.toUpperCase()})`;
                output.appendChild(title);

                if (data.sequence && data.sequence.length) {
                    const seqContainer = document.createElement('div');
                    seqContainer.style = 'margin-bottom:20px; border-left:4px solid #007cba; padding-left:20px;';
                    data.sequence.forEach((step, idx) => {
                        const det = document.createElement('details');
                        det.style.marginBottom = '10px';
                        const sum = document.createElement('summary');
                        sum.style = 'cursor:pointer; font-weight:bold; color:#007cba;';
                        sum.textContent = `Mind ${idx+1} Reflection`;
                        const inner = document.createElement('div');
                        inner.style = 'padding:10px; background:#f0f8ff; border-radius:8px; font-size:13px; margin-top:5px;';
                        inner.innerHTML = sanitizeHTML(typeof step === 'string' ? step : step.content);
                        det.appendChild(sum);
                        det.appendChild(inner);
                        seqContainer.appendChild(det);
                    });
                    output.appendChild(seqContainer);
                }

                const finalTitle = document.createElement('h4');
                finalTitle.textContent = 'Final Strategy Output';
                output.appendChild(finalTitle);

                const finalContent = document.createElement('div');
                finalContent.innerHTML = sanitizeHTML(data.final_output);
                output.appendChild(finalContent);
            }
        });
    });

    // Handle Toggle Permission
    window.ammTogglePerm = function(userId, teamId, perm, isChecked) {
        // In real app, you'd fetch existing perms first, but for now we toggle based on common sense
        const perms = ['can_generate', 'can_view_workspace'];
        if (!isChecked) perms.splice(perms.indexOf(perm), 1);

        fetch(apiRoot + '/update-member-role', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
            body: JSON.stringify({ user_id: userId, team_id: teamId, permissions: perms })
        });
    };

    // Handle Create Team
    if (document.getElementById('amm-create-team-btn')) {
        document.getElementById('amm-create-team-btn').addEventListener('click', () => {
            const name = prompt('Enter Team Name:');
            if(!name) return;
            fetch(apiRoot + '/create-team', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
                body: JSON.stringify({ name })
            }).then(res => res.json()).then(data => { if(data.success) location.reload(); });
        });
    }

    // Handle Save Settings
    document.getElementById('amm-save-settings-btn').addEventListener('click', () => {
        safeFetch('/update-settings', {
            method: 'POST',
            body: JSON.stringify({
                webhook_url: document.getElementById('amm-set-webhook').value,
                company_name: document.getElementById('amm-set-company').value,
                default_mind: document.getElementById('amm-set-default-mind').value,
                knowledge_base: document.getElementById('amm-set-kb').value,
                usage_alerts: document.getElementById('amm-set-alerts').checked
            })
        }).then(data => { if(data.success) { alert('Settings Saved!'); location.reload(); } });
    });

    window.ammCheckout = function(planId) {
        safeFetch('/checkout', {
            method: 'POST',
            body: JSON.stringify({ plan_id: planId, gateway: 'stripe' })
        }).then(data => { if(data.url) window.location.href = data.url; });
    };
});
